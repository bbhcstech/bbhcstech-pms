<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\EmployeeCreatedNotification;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\Designation;
use App\Models\ParentDepartment;
use App\Models\Department;
use App\Models\Country;
use App\Mail\EmployeeInvite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Simple role guard for admin-only methods.
     */
    protected function ensureAdmin()
    {
        $user = auth()->user();
        if (! $user || ($user->role ?? '') !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    // ===========================================================
    // ===== REPORTING-TO INTEGRITY : subordinate check helper ===
    // ===========================================================

    private function hasSubordinates(int $userId): bool
    {
        return EmployeeDetail::where('reporting_to', $userId)->exists();
    }

    /**
     * List employees with optional filters.
     *
     */
    public function index(Request $request)
    {
        $query = User::with(['employeeDetail.designation', 'employeeDetail.department'])
            ->where('role', 'employee');

        if ($request->filled('employee_id')) {
            $query->whereHas('employeeDetail', function ($q) use ($request) {
                $q->where('employee_id', 'like', '%' . $request->employee_id . '%');
            });
        }

        if ($request->filled('designation_id')) {
            $query->whereHas('employeeDetail', function ($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        // Exclude users whose employeeDetail indicates they are on notice or on probation.
        $query->whereDoesntHave('employeeDetail', function ($q) {
            $q->whereIn('status', ['notice', 'probation'])
              ->orWhereNotNull('notice_end_date')
              ->orWhereNotNull('probation_end_date');
        });

        // include subordinate count for reporting-to integrity UI
        $query->select(
            'users.*',
            DB::raw('(SELECT COUNT(*) FROM employee_details ed WHERE ed.reporting_to = users.id) AS subordinate_count')
        );

        // FIXED: Changed from get() to paginate() for pagination to work
        $employees = $query->orderBy('created_at', 'desc')->paginate(15);

        // prepare dropdown list options but exclude notice/probation entries so selects don't show them
        $employeeDetails = EmployeeDetail::with(['user', 'reportingTo'])->get()
            ->filter(function ($d) {
                $status = $d->status ?? null;
                $hasNotice = !empty($d->notice_end_date);
                $hasProb = !empty($d->probation_end_date);

                if (in_array($status, ['notice', 'probation'])) return false;
                if ($hasNotice || $hasProb) return false;
                return true;
            });

        return view('admin.employees.index', [
            'employees' => $employees,
            'designations' => Designation::orderBy('name')->get(),
            'employee_data' => User::all(),
            'employeeDetails' => $employeeDetails,
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => route('dashboard')],
                ['title' => 'Employees', 'url' => route('employees.index')],
            ]
        ]);
    }

    /**
     * Show create employee form.
     */
    public function create()
    {
        $this->ensureAdmin();

        // compute preview id (does NOT reserve it — store() will recompute to avoid races)
        $nextEmployeeId = $this->computeNextEmployeeIdWithLock();

        return view('admin.employees.create', [
            'designations'    => Designation::orderBy('name')->get(),
            'departments'     => Department::with('parent')->orderBy('dpt_name')->get(),
            'prtdepartments'  => ParentDepartment::orderBy('dpt_name')->get(),
            'users'           => User::where('role', 'employee')
                                    ->whereHas('employeeDetail', function ($q) {
                                        $q->where('status', 'Active');
                                    })
                                    ->orderBy('name')
                                    ->get(),
            'countries'       => Country::orderBy('name')->get(),
            'employee'        => null,
            'nextEmployeeId'  => $nextEmployeeId,
        ]);
    }

    /**
     * AJAX endpoint: check mobile number uniqueness
     */
    public function checkMobile(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'mobile' => 'required|string|regex:/^[1-9]\d{9}$/',
            'employee_id' => 'nullable|integer|exists:users,id'
        ]);

        $mobile = $request->mobile;
        $mobileWithCode = '+91' . $mobile;
        $currentId = $request->employee_id;

        $query = User::where('mobile', $mobileWithCode);

        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'mobile' => $mobile
        ]);
    }

    /**
     * AJAX endpoint: return next employee id (json)
     */
    public function nextId()
    {
        $this->ensureAdmin(); // keep same guard as other endpoints
        return response()->json(['next' => $this->computeNextEmployeeIdWithLock()]);
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        // ==============================================
        // CRITICAL FIX: EXIT DATE VALIDATION LOGIC
        // ==============================================
        if ($request->status === 'Inactive' && empty($request->exit_date)) {
            return back()->withErrors(['exit_date' => 'Exit date is required when status is Inactive.'])->withInput();
        }

        if ($request->exit_date) {
            $exitDate = Carbon::parse($request->exit_date);
            $joiningDate = Carbon::parse($request->joining_date);

            // Exit date must be after joining date
            if ($exitDate->lte($joiningDate)) {
                return back()->withErrors(['exit_date' => 'Exit date must be after joining date.'])->withInput();
            }

            // Auto-set login_allowed based on exit date
            $today = Carbon::today();
            if ($today->gte($exitDate)) {
                // Exit date passed - block login
                $request->merge(['login_allowed' => 0]);
            } else {
                // Exit date future - allow login
                $request->merge(['login_allowed' => 1]);
            }
        }

        // Prepare validation rules
        $validationRules = [
            'name'              => 'required|string',
            'email'             => 'required|email|unique:users,email',
            'mobile'            => 'required|regex:/^[1-9]\d{9}$/|unique:users,mobile',
            'joining_date'      => 'required|date',
            'business_address'  => 'required|string',
            'status'            => 'required|in:Active,Inactive',
            'login_allowed'     => 'required|in:0,1',
            'password'          => 'nullable|string|min:8',
            'profile_picture'   => 'nullable|image|max:2048',

            'probation_end_date' => 'nullable|date',
            'notice_start_date'  => 'nullable|date',
            'notice_end_date'    => 'nullable|date',
        ];

        // If editing, adjust unique rules
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $userId = $request->route('employee');
            $validationRules['email'] = 'required|email|unique:users,email,' . $userId;
            $validationRules['mobile'] = 'required|regex:/^[1-9]\d{9}$/|unique:users,mobile,' . $userId;
        }

        $request->validate($validationRules);

        $profileImagePath = null;

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $fileName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('admin/uploads/profile-images'), $fileName);
            $profileImagePath = 'admin/uploads/profile-images/' . $fileName;
        }

        // generate a plain temporary password (if request provided use it, else random)
        $plainPassword = $request->filled('password') ? $request->password : Str::random(12);
        $passwordHash = Hash::make($plainPassword);

        DB::beginTransaction();
        try {
            // Format mobile number with +91 prefix
            $mobileWithCode = '+91' . $request->mobile;

            // Create user
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'mobile'        => $mobileWithCode, // Store with +91 prefix
                'password'      => $passwordHash,
                'role'          => $request->user_role ?? 'employee',
                'profile_image' => $profileImagePath,
                'login_allowed' => $request->login_allowed ?? 1,
                'email_notifications' => $request->email_notifications ?? 1,
            ]);

            // Prepare employee detail payload
            $employeeData = $request->only([
                'designation_id', 'parent_dpt_id', 'department_id', 'employee_id',
                'salutation', 'country', 'gender', 'joining_date', 'dob', 'reporting_to',
                'language', 'user_role', 'address', 'about',
                'hourly_rate', 'slack_member_id', 'skills',
                'probation_end_date', 'notice_start_date', 'notice_end_date',
                'employment_type', 'marital_status', 'business_address', 'status', 'exit_date'
            ]);

            // Add mobile without prefix for employee detail
            $employeeData['mobile'] = $request->mobile;
            $employeeData['user_id'] = $user->id;

            // ================================================
            // FIXED: Handle new designation with firstOrCreate - WITH LEVEL
            // ================================================
            if ($request->designation_id === 'new' && $request->filled('new_designation')) {
                $designation = Designation::firstOrCreate(
                    ['name' => trim($request->new_designation)],
                    [
                        'level' => $request->new_designation_level ?? 0, // ADDED LEVEL FIELD
                        'status' => 'Active',
                        'added_by' => auth()->id(),
                        'last_updated_by' => auth()->id(),
                    ]
                );
                $employeeData['designation_id'] = $designation->id;
            } else {
                $employeeData['designation_id'] = $request->designation_id;
            }

            // ================================================
            // FIXED: Handle new department with firstOrCreate
            // ================================================
            if ($request->parent_dpt_id === 'new' && $request->filled('new_department')) {
                $department = ParentDepartment::firstOrCreate(
                    ['dpt_name' => trim($request->new_department)],
                    [
                        'dpt_code' => $this->generateNextParentDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $employeeData['parent_dpt_id'] = $department->id;
            } else {
                $employeeData['parent_dpt_id'] = $request->parent_dpt_id;
            }

            // ================================================
            // FIXED: Handle new sub-department with firstOrCreate
            // ================================================
            if ($request->department_id === 'new' && $request->filled('new_sub_department')) {
                $subDepartment = Department::firstOrCreate(
                    [
                        'dpt_name' => trim($request->new_sub_department),
                        'parent_dpt_id' => $employeeData['parent_dpt_id']
                    ],
                    [
                        'dpt_code' => $this->generateNextSubDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $employeeData['department_id'] = $subDepartment->id;
            } else {
                $employeeData['department_id'] = $request->department_id;
            }

            // ================================================
            // FIXED: Handle new country with firstOrCreate
            // ================================================
            if ($request->country === 'new' && $request->filled('new_country')) {
                $country = Country::firstOrCreate(
                    ['name' => trim($request->new_country)],
                    [
                        'status' => 'Active',
                        'added_by' => auth()->id()
                    ]
                );
                $employeeData['country'] = $country->name;
            } else {
                $employeeData['country'] = $request->country;
            }

            // Normalize: if probation_end_date provided, clear notice dates; if notice provided, clear probation
            if (!empty($employeeData['probation_end_date'])) {
                $employeeData['notice_start_date'] = null;
                $employeeData['notice_end_date'] = null;
            } elseif (!empty($employeeData['notice_start_date']) || !empty($employeeData['notice_end_date'])) {
                $employeeData['probation_end_date'] = null;
            }

            // Create EmployeeDetail with retry to handle rare employee_id collision
            $tries = 0;
            $created = false;
            do {
                $employeeData['employee_id'] = $this->computeNextEmployeeIdWithLock();
                try {
                    EmployeeDetail::create($employeeData);
                    $created = true;
                } catch (\Illuminate\Database\QueryException $qe) {
                    $tries++;
                    Log::warning('employee_id collision on create, retrying', [
                        'employee_id' => $employeeData['employee_id'],
                        'tries' => $tries,
                        'error' => $qe->getMessage()
                    ]);
                    if ($tries > 5) {
                        // rethrow after too many retries
                        throw $qe;
                    }
                    usleep(100000); // 100ms backoff
                }
            } while (! $created);

            // Send email to employee immediately (synchronous) so mail goes to employee account now
            try {
                // send immediately, bypassing queue (useful if you want immediate delivery)
                Notification::sendNow($user, new EmployeeCreatedNotification($user, $plainPassword));

                Log::info('EmployeeCreatedNotification sent immediately', [
                    'email' => $user->email,
                    'user_id' => $user->id
                ]);
            } catch (\Exception $mailEx) {
                Log::error('Employee creation email failed', [
                    'email' => $user->email,
                    'error' => $mailEx->getMessage()
                ]);
                // proceed without failing the whole operation; client will still see success
            }

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee added successfully and notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store employee error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Edit form.
     */
    public function edit($id)
    {
        $this->ensureAdmin();

        $employee = User::with('employeeDetail')->findOrFail($id);

        return view('admin.employees.edit', [
            'employee' => $employee,
            'designations' => Designation::orderBy('name')->get(),
            'departments' => Department::all(),
            'users' => User::where('role', 'employee')
                ->whereHas('employeeDetail', function ($q) {
                    $q->where('status', 'Active');
                })
                ->orderBy('name')->get(),
            'countries' => Country::all(),
            'prtdepartments' => ParentDepartment::latest()->get(),
        ]);
    }

    /**
     * Store a new Department (called from the Add Department modal)
     */
    public function storeDepartment(Request $request)
    {
        // basic validation for dpt_name first
        $request->validate([
            'dpt_name' => 'required|string|max:191',
            'parent_dpt_id' => 'nullable', // we'll validate existence with custom logic below
        ]);

        // custom existence check: allow parent_dpt_id to exist in either parent_departments.id OR departments.id
        $parentId = $request->input('parent_dpt_id', null);
        if (!is_null($parentId) && $parentId !== '') {
            $existsInParent = \DB::table('parent_departments')->where('id', $parentId)->exists();
            $existsInDepartments = \DB::table('departments')->where('id', $parentId)->exists();

            if (! $existsInParent && ! $existsInDepartments) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected parent department does not exist.'
                ], 422);
            }
        } else {
            $parentId = null;
        }

        DB::beginTransaction();
        try {
            // Generate next sequential sub-department code (matching DepartmentController logic)
            $departmentCode = $this->generateNextSubDepartmentCode();

            $dpt = Department::create([
                'dpt_name' => $request->input('dpt_name'),
                'parent_dpt_id' => $parentId,
                'dpt_code' => $departmentCode,
                'added_by' => auth()->id() ?? null,
                'last_updated_by' => auth()->id() ?? null,
                'status' => 'Active' // Added status field
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'dpt' => [
                    'id' => $dpt->id,
                    'dpt_name' => $dpt->dpt_name,
                    'parent_dpt_id' => $dpt->parent_dpt_id,
                    // parent_name: try to fetch from parent_departments first, then from departments as fallback
                    'parent_name' => optional(\DB::table('parent_departments')->where('id', $dpt->parent_dpt_id)->first())->dpt_name
                                     ?? optional(\DB::table('departments')->where('id', $dpt->parent_dpt_id)->first())->dpt_name
                                     ?? null,
                    'dpt_code' => $dpt->dpt_code,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storeDepartment error', ['error' => $e->getMessage(), 'payload' => $request->all()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Server error while creating department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return sub-departments for a parent department (AJAX)
     */
    public function getSubDepartments($parentId)
    {
        // ensure numeric parent id
        if (!is_numeric($parentId)) {
            return response()->json([], 400);
        }

        $subs = Department::where('parent_dpt_id', $parentId)
                ->orderBy('dpt_name')
                ->get(['id', 'dpt_name', 'dpt_code']);

        return response()->json($subs);
    }

    /**
     * Update employee.
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        // ==============================================
        // CRITICAL FIX: EXIT DATE VALIDATION LOGIC
        // ==============================================
        if ($request->status === 'Inactive' && empty($request->exit_date)) {
            return back()->withErrors(['exit_date' => 'Exit date is required when status is Inactive.'])->withInput();
        }

        if ($request->exit_date) {
            $exitDate = Carbon::parse($request->exit_date);
            $joiningDate = Carbon::parse($request->joining_date);

            // Exit date must be after joining date
            if ($exitDate->lte($joiningDate)) {
                return back()->withErrors(['exit_date' => 'Exit date must be after joining date.'])->withInput();
            }

            // Auto-set login_allowed based on exit date
            $today = Carbon::today();
            if ($today->gte($exitDate)) {
                // Exit date passed - block login
                $request->merge(['login_allowed' => 0]);
            } else {
                // Exit date future - allow login
                $request->merge(['login_allowed' => 1]);
            }
        }

        // load user & detail first so we can build validation rules that ignore current records
        $user = User::findOrFail($id);
        $detail = $user->employeeDetail;

        // build unique rules (ignore current user/detail if present)
        $emailUniqueRule = 'required|email|unique:users,email,' . $user->id;
        $mobileUniqueRule = 'required|regex:/^[1-9]\d{9}$/|unique:users,mobile,' . $user->id;
        $employeeIdRule = 'required|string';
        if ($detail) {
            $employeeIdRule .= '|unique:employee_details,employee_id,' . $detail->id;
        } else {
            $employeeIdRule .= '|unique:employee_details,employee_id';
        }

        $request->validate([
            'employee_id'      => $employeeIdRule,
            'name'             => 'required|string',
            'email'            => $emailUniqueRule,
            'mobile'           => $mobileUniqueRule,
            'business_address' => 'required|string',
            'status'           => 'required|in:Active,Inactive',
            'login_allowed'    => 'required|in:0,1',
            'department_id'    => 'nullable|exists:departments,id',
            'profile_picture'  => 'nullable|image|max:2048',
            'probation_end_date' => 'nullable|date',
            'notice_start_date'  => 'nullable|date',
            'notice_end_date'    => 'nullable|date',
            'reporting_to'       => 'nullable|integer|exists:users,id',
        ]);

        // Small helper: check if $potentialAncestorId is an ancestor (manager chain) of $startUserId
        $isAncestor = function (int $potentialAncestorId, int $startUserId): bool {
            $current = $startUserId;
            while ($current) {
                $ed = EmployeeDetail::where('user_id', $current)->first();
                if (! $ed || empty($ed->reporting_to)) {
                    return false;
                }
                if (intval($ed->reporting_to) === intval($potentialAncestorId)) {
                    return true;
                }
                $current = intval($ed->reporting_to);
            }
            return false;
        };

        // Validate reporting_to: cannot report to self or to any of user's subordinates (prevent cycles)
        $reportingTo = $request->input('reporting_to');
        if (!empty($reportingTo)) {
            $reportingTo = intval($reportingTo);
            if ($reportingTo === intval($user->id)) {
                return back()->withErrors(['reporting_to' => 'Employee cannot report to themselves.'])->withInput();
            }

            // if the chosen manager reports (directly or indirectly) to this user -> disallow
            if ($isAncestor($user->id, $reportingTo)) {
                return back()->withErrors(['reporting_to' => 'Cannot set reporting manager to a subordinate (would create a cycle).'])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Format mobile number with +91 prefix
            $mobileWithCode = '+91' . $request->mobile;

            // handle profile image: delete old file if present and save new one
            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                // delete old
                if ($user->profile_image && file_exists(public_path($user->profile_image))) {
                    @unlink(public_path($user->profile_image));
                }
                $fileName = time() . '-' . $image->getClientOriginalName();
                $image->move(public_path('admin/uploads/profile-images'), $fileName);
                $user->profile_image = 'admin/uploads/profile-images/' . $fileName;
            }

            // update user
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $mobileWithCode; // Store with +91 prefix
            $user->login_allowed = $request->login_allowed ?? 1;
            $user->email_notifications = $request->email_notifications ?? 1;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // ensure detail exists
            if (! $detail) {
                $detail = new EmployeeDetail();
                $detail->user_id = $user->id;
            }

            // prepare employee detail payload
            $data = $request->only([
                'designation_id', 'parent_dpt_id', 'department_id', 'employee_id',
                'salutation', 'country', 'gender', 'joining_date', 'dob', 'reporting_to',
                'language', 'user_role', 'address', 'about',
                'hourly_rate', 'slack_member_id', 'skills',
                'probation_end_date', 'notice_start_date', 'notice_end_date',
                'employment_type', 'marital_status', 'business_address', 'status', 'exit_date'
            ]);

            // Add mobile without prefix for employee detail
            $data['mobile'] = $request->mobile;

            // normalize: empty department -> null
            if (empty($data['department_id'])) {
                $data['department_id'] = null;
            }

            // FIXED: Handle new designation with level
            if ($request->designation_id === 'new' && $request->filled('new_designation')) {
                $designation = Designation::firstOrCreate(
                    ['name' => trim($request->new_designation)],
                    [
                        'level' => $request->new_designation_level ?? 0, // ADDED LEVEL FIELD
                        'status' => 'Active',
                        'added_by' => auth()->id(),
                        'last_updated_by' => auth()->id(),
                    ]
                );
                $data['designation_id'] = $designation->id;
            }

            // FIXED: Handle new department with firstOrCreate
            if ($request->parent_dpt_id === 'new' && $request->filled('new_department')) {
                $department = ParentDepartment::firstOrCreate(
                    ['dpt_name' => trim($request->new_department)],
                    [
                        'dpt_code' => $this->generateNextParentDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $data['parent_dpt_id'] = $department->id;
            }

            // FIXED: Handle new sub-department with firstOrCreate
            if ($request->department_id === 'new' && $request->filled('new_sub_department')) {
                $subDepartment = Department::firstOrCreate(
                    [
                        'dpt_name' => trim($request->new_sub_department),
                        'parent_dpt_id' => $data['parent_dpt_id']
                    ],
                    [
                        'dpt_code' => $this->generateNextSubDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $data['department_id'] = $subDepartment->id;
            }

            // FIXED: Handle new country with firstOrCreate
            if ($request->country === 'new' && $request->filled('new_country')) {
                $country = Country::firstOrCreate(
                    ['name' => trim($request->new_country)],
                    [
                        'status' => 'Active',
                        'added_by' => auth()->id()
                    ]
                );
                $data['country'] = $country->name;
            }

            // Normalize dates: if probation_end_date provided, clear notice dates; if notice provided, clear probation
            if (!empty($data['probation_end_date'])) {
                $data['notice_start_date'] = null;
                $data['notice_end_date'] = null;
            } elseif (!empty($data['notice_start_date']) || !empty($data['notice_end_date'])) {
                $data['probation_end_date'] = null;
            }

            // If employee_id is empty for some reason, compute a new one (keeps parity with create/store)
            if (empty($data['employee_id'])) {
                $data['employee_id'] = $this->computeNextEmployeeIdWithLock();
            }

            // mass-assign allowed fields then save
            $detail->fill($data);
            $detail->save();

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update employee error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete employee.
     */
    public function destroy($id)
    {
        $this->ensureAdmin();

        // ======================================================
        // ===== REPORTING-TO INTEGRITY : block protected delete
        // ======================================================
        if ($this->hasSubordinates($id)) {
            return redirect()->route('employees.index')
                ->withErrors(['error' => 'You cannot delete this employee because other employees report to them.']);
        }

        $user = User::findOrFail($id);

        if ($user->employeeDetail) {
            $user->employeeDetail->delete();
        }

        $user->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted.');
    }

    /**
     * Show single employee.
     */
    public function show($id)
    {
        $employee = User::with([
            'employeeDetail.designation',
            'employeeDetail.department',
            'employeeDetail.reportingTo'
        ])->findOrFail($id);

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Bulk status update.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'employee_ids' => 'required|array',
            'status' => 'required|in:Active,Inactive',
        ]);

        // If setting to Inactive, check for exit dates
        if ($request->status === 'Inactive') {
            $employeesWithoutExitDate = EmployeeDetail::whereIn('user_id', $request->employee_ids)
                ->whereNull('exit_date')
                ->get();

            if ($employeesWithoutExitDate->count() > 0) {
                return response()->json([
                    'message' => 'Some employees do not have an exit date. Exit date is required for Inactive status.',
                    'employees' => $employeesWithoutExitDate->pluck('employee_id')
                ], 422);
            }
        }

        EmployeeDetail::whereIn('user_id', $request->employee_ids)
            ->update(['status' => $request->status]);

        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * Bulk delete.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:users,id'
        ]);

        DB::beginTransaction();
        try {

            // ==========================================================
            // ===== REPORTING-TO INTEGRITY : block protected deletions
            // ==========================================================
            $blocked = [];
            $allowed = [];

            foreach ($request->employee_ids as $id) {
                if ($this->hasSubordinates($id)) {
                    $blocked[] = $id;
                } else {
                    $allowed[] = $id;
                }
            }

            // delete only employees with no subordinates
            EmployeeDetail::whereIn('user_id', $allowed)->delete();
            User::whereIn('id', $allowed)->delete();

            DB::commit();

            if (count($blocked) > 0) {
                return response()->json([
                    'message'     => 'Some employees cannot be deleted because they have subordinates.',
                    'blocked_ids' => $blocked
                ], 409);
            }

            return response()->json(['message' => 'Selected employees deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error deleting employees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send employee invite.
     */
    public function sendInvite(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        // Sanitize email
        $rawEmail = (string) $request->input('email', '');
        $sanitized = trim($rawEmail);
        $sanitized = preg_replace('/^(www\.|mailto:)/i', '', $sanitized);
        $request->merge(['email' => $sanitized]);

        $request->validate([
            'email' => 'required|email:rfc,dns',
            'message' => 'nullable|string|max:1000'
        ]);

        Log::info('Invite request received', [
            'original'  => $rawEmail,
            'sanitized' => $sanitized
        ]);

        DB::beginTransaction();

        try {
            $email = $request->email;

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => Str::before($email, '@'),
                    'password' => Hash::make(Str::random(12)),
                    'role' => 'employee',
                    'login_allowed' => 1 // Default to allowed
                ]
            );

            if (!$user->employeeDetail) {
                EmployeeDetail::create([
                    'user_id' => $user->id,
                    'status' => 'Active',
                    'business_address' => '',
                    'employee_id' => $this->computeNextEmployeeIdWithLock()
                ]);
            }

            DB::commit();

            $inviteUrl = URL::temporarySignedRoute(
                'employees.invite.accept',
                now()->addDays(7),
                ['user' => $user->id]
            );

            try {
                Mail::to($user->email)->send(
                    new \App\Mail\EmployeeInvite($user, $request->message, $inviteUrl)
                );

                Log::info('Invite sent via SMTP', ['to' => $user->email]);

                return response()->json(['message' => 'Invite sent successfully']);
            } catch (\Exception $e) {
                Log::error('SMTP email failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Invite created but email sending failed',
                    'error' => $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invite error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error sending invite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept invite view.
     */
    public function acceptInvite(Request $request)
    {
        $user = User::with('employeeDetail')->findOrFail($request->user);

        return view('auth.employee-invite-accept', compact('user'));
    }

    /**
     * Accept invite submit.
     */
    public function acceptInviteSubmit(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required',
            'password' => 'required|min:9|confirmed'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name,
            'password' => Hash::make($request->password)
        ]);

        $detail = $user->employeeDetail;
        $detail->status = 'Active';

        if (empty($detail->employee_id)) {
            // computeNextEmployeeIdWithLock is transactional internally now
            $detail->employee_id = $this->computeNextEmployeeIdWithLock();
        }

        $detail->save();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account activated.');
    }

    /**
     * AJAX: create a designation (used by the employee create page "Add" button).
     * Returns JSON { designation: { id, name, unique_code, parent_id, ... } } on success.
     */
    public function storeDesignation(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['name', 'parent_id', 'status']);

        $validator = \Validator::make($data, [
            'name'      => 'required|string|max:191|unique:designations,name',
            'parent_id' => 'nullable|exists:designations,id',
            'status'    => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {

            $designation = \App\Models\Designation::create([
                'name' => $data['name'],
                'parent_id' => $data['parent_id'] ?? null,
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id()
            ]);

            $designation->refresh();

            return response()->json([
                'status' => 'success',
                'designation' => $designation
            ], 201);

        } catch (\Exception $e) {

            \Log::error('Designation AJAX failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Could not create designation'
            ], 500);
        }
    }


    /**
     * AJAX: create a country (used by the employee create page "Add" button).
     * Returns JSON { country: { id, name, ... } } on success.
     */
    public function storeCountry(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['name', 'status']);

        $validator = \Validator::make($data, [
            'name'   => 'required|string|max:191|unique:countries,name',
            'status' => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $country = Country::create([
                'name' => $data['name'],
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'country' => $country
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to store country via AJAX', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create country'
            ], 500);
        }
    }

    /**
     * AJAX: create a parent department (used by the employee create page "Add" button).
     * Returns JSON { department: { id, dpt_name, dpt_code, ... } } on success.
     */
    public function storeParentDepartment(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['dpt_name', 'status']);

        $validator = \Validator::make($data, [
            'dpt_name' => 'required|string|max:191|unique:parent_departments,dpt_name',
            'status'   => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $departmentCode = $this->generateNextParentDepartmentCode();

            $department = ParentDepartment::create([
                'dpt_name' => $data['dpt_name'],
                'dpt_code' => $departmentCode,
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id() ?? null
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'department' => $department
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store parent department via AJAX', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create department'
            ], 500);
        }
    }

    /**
     * AJAX: create a sub-department (used by the employee create page "Add" button).
     * Returns JSON { department: { id, dpt_name, dpt_code, parent_dpt_id, ... } } on success.
     */
    public function storeSubDepartment(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['dpt_name', 'parent_dpt_id', 'status']);

        $validator = \Validator::make($data, [
            'dpt_name'      => 'required|string|max:191',
            'parent_dpt_id' => 'required|exists:parent_departments,id',
            'status'        => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $departmentCode = $this->generateNextSubDepartmentCode();

            $department = Department::create([
                'dpt_name' => $data['dpt_name'],
                'parent_dpt_id' => $data['parent_dpt_id'],
                'dpt_code' => $departmentCode,
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id() ?? null
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'department' => $department
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store sub-department via AJAX', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create sub-department'
            ], 500);
        }
    }

    /**
     * Compute next employee ID.
     */
    private function computeNextEmployeeIdWithLock(): string
    {
        $prefix = 'BBH';
        $year = date('Y');
        $digits = 3;

        $like = $prefix . $year . '%';

        return DB::transaction(function () use ($like, $prefix, $year, $digits) {
            $last = DB::table('employee_details')
                ->where('employee_id', 'LIKE', $like)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last && preg_match('/(\d{3})$/', $last->employee_id, $m)) {
                $nextNumber = intval($m[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            return $prefix . $year . str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);
        }, 5); // retry up to 5 times on deadlock
    }

    /**
     * Generate next sequential parent department code (DEP-0001, DEP-0002, etc.)
     */
    private function generateNextParentDepartmentCode()
    {
        $prefix = 'DEP-';  // Prefix for parent departments

        return DB::transaction(function () use ($prefix) {
            // Lock for update to prevent concurrent insertions
            $last = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
                $nextNumber = (int)$m[1] + 1;
            } else {
                $nextNumber = 1;
            }

            // Determine padding (4 digits for 0001 format)
            $pad = 4;

            $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

            // EXTRA CHECK: If duplicate somehow exists
            while (ParentDepartment::where('dpt_code', $generatedCode)->exists()) {
                $nextNumber++;
                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            return $generatedCode;
        }, 5); // retry up to 5 times on deadlock
    }

    /**
     * Generate next sequential sub-department code (SUB-01, SUB-02, etc.)
     */
    private function generateNextSubDepartmentCode()
    {
        $prefix = 'SUB-';  // SAME prefix as in DepartmentController

        return DB::transaction(function () use ($prefix) {
            // Lock row for concurrency safety - same logic as DepartmentController
            $last = Department::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
                $nextNumber = (int)$m[1] + 1;
            } else {
                $nextNumber = 1;
            }

            // Determine padding (2 digits for 01, 02... 3 digits for 100+)
            $pad = $nextNumber > 99 ? 3 : 2;

            $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

            // EXTRA CHECK: If duplicate somehow exists
            while (Department::where('dpt_code', $generatedCode)->exists()) {
                $nextNumber++;
                $pad = $nextNumber > 99 ? 3 : $pad;
                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            return $generatedCode;
        }, 5); // retry up to 5 times on deadlock
    }

    /**
     * NEW METHOD: Auto-inactivate employees when exit date passes
     */
    public function checkExitDates()
    {
        $today = Carbon::today();

        $employeesToInactivate = EmployeeDetail::where('status', 'Active')
            ->whereNotNull('exit_date')
            ->whereDate('exit_date', '<=', $today)
            ->get();

        $count = 0;

        foreach ($employeesToInactivate as $employee) {
            $employee->status = 'Inactive';
            $employee->save();
            $count++;

            Log::info('Auto-inactivated employee based on exit date', [
                'employee_id' => $employee->employee_id,
                'user_id' => $employee->user_id,
                'exit_date' => $employee->exit_date
            ]);
        }

        return $count;
    }
}
