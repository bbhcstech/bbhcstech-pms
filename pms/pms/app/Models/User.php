<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'mobile',
        'designation',
        'gender',
        'dob',
        'marital_status',
        'address',
        'about',
        'country',
        'language',
        'slack_id',
        'email_notify',
        'google_calendar',
        'profile_image',
        'login_allowed',
        'email_notifications',
        // ADD THESE NEW FIELDS:
        'joining_date',
        'annual_leave_balance',
        'leaves_taken_this_year',
        'remaining_leaves',
        'leave_amount',
        'last_leave_reset',
        'carry_forward_leaves',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'login_allowed' => 'boolean',
            'email_notifications' => 'boolean',
            // ADD THESE NEW CASTS:
            'joining_date' => 'date',
            'last_leave_reset' => 'date',
            'annual_leave_balance' => 'integer',
            'leaves_taken_this_year' => 'integer',
            'remaining_leaves' => 'integer',
            'leave_amount' => 'decimal:2',
            'carry_forward_leaves' => 'integer',
        ];
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class, 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(\App\Models\Project::class, 'project_user', 'user_id', 'project_id')
                    ->withPivot('hourly_rate', 'role')
                    ->withTimestamps();
    }

    // ADD THESE NEW RELATIONSHIPS FOR LEAVE SYSTEM:
    public function leaveBalances()
    {
        return $this->hasMany(\App\Models\LeaveBalance::class);
    }

    public function currentYearBalance()
    {
        $currentYear = date('Y');
        return $this->hasOne(\App\Models\LeaveBalance::class)->where('year', $currentYear);
    }

    /**
     * Calculate pro-rated leaves based on joining date
     */
    public function calculateProRatedLeaves($annualLeaves = 18, $fiscalYearStart = '04-01')
    {
        if (!$this->joining_date) {
            return $annualLeaves; // Full leaves if no joining date
        }

        $joinDate = Carbon::parse($this->joining_date);
        $currentYear = date('Y');

        // Determine fiscal year
        $fiscalStart = Carbon::createFromFormat('m-d', $fiscalYearStart)->year($currentYear);

        // If current date is before fiscal start, use previous year
        if (Carbon::now()->lt($fiscalStart)) {
            $fiscalStart = $fiscalStart->subYear();
        }

        $fiscalEnd = $fiscalStart->copy()->addYear()->subDay();

        // If employee joined after fiscal year start
        if ($joinDate->gt($fiscalStart)) {
            $monthsRemaining = $joinDate->diffInMonths($fiscalEnd);
            if ($monthsRemaining > 0) {
                $proRatedLeaves = floor(($annualLeaves / 12) * $monthsRemaining);
                return max(1, $proRatedLeaves); // At least 1 leave
            }
            return 0;
        }

        return $annualLeaves; // Joined before fiscal year start
    }

    /**
     * Get leave utilization percentage
     */
    public function getLeaveUtilizationPercentage()
    {
        if ($this->annual_leave_balance <= 0) {
            return 0;
        }

        $used = $this->leaves_taken_this_year;
        $total = $this->annual_leave_balance;

        return ($used / $total) * 100;
    }

    /**
     * Get leave status color
     */
    public function getLeaveStatusColor()
    {
        $percentage = $this->getLeaveUtilizationPercentage();

        if ($percentage >= 90) {
            return 'danger';
        } elseif ($percentage >= 75) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    /**
     * Get monetary value of remaining leaves
     */
    public function getRemainingLeaveValue()
    {
        if (!$this->leave_amount) {
            return 0;
        }

        return $this->remaining_leaves * $this->leave_amount;
    }

    /**
     * Check if employee can take paid leave
     */
    public function canTakePaidLeave($requestedDays = 1)
    {
        return $this->remaining_leaves >= $requestedDays;
    }

    /**
     * Get days until next leave reset
     */
    public function getDaysUntilReset()
    {
        if (!$this->last_leave_reset) {
            return 0;
        }

        $nextReset = Carbon::parse($this->last_leave_reset)->addYear();
        return Carbon::now()->diffInDays($nextReset, false);
    }

    /**
     * CRITICAL FIX: Employee can login BASED ON EXIT DATE
     * - Inactive status but exit date in FUTURE = CAN LOGIN
     * - Active/Inactive with exit date passed = CANNOT LOGIN
     */
    public function canLogin()
    {
        // First check login_allowed
        if (!$this->login_allowed) {
            return false;
        }

        $employeeStatus = $this->employeeDetail ? $this->employeeDetail->status : 'Active';

        // Check if employee has exit date
        if ($this->employeeDetail && $this->employeeDetail->exit_date) {
            $today = Carbon::today();
            $exitDate = Carbon::parse($this->employeeDetail->exit_date);

            // LOGIC: Can login ONLY if today < exit_date
            // (BEFORE exit date, NOT ON or AFTER)
            return $today->lt($exitDate); // $today < $exit_date
        }

        // If no exit date:
        // - Active status = CAN login
        // - Inactive status = CANNOT login
        return $employeeStatus === 'Active';
    }

    /**
     * Get specific error message for login restriction
     */
    public function getLoginErrorMessage()
    {
        $loginAllowed = (bool) $this->login_allowed;
        $employeeStatus = $this->employeeDetail ? $this->employeeDetail->status : 'Active';

        // Check login_allowed first
        if (!$loginAllowed) {
            return 'Your account is active but login is blocked by admin. Please contact administrator.';
        }

        // Check exit date logic
        if ($this->employeeDetail && $this->employeeDetail->exit_date) {
            $today = Carbon::today();
            $exitDate = Carbon::parse($this->employeeDetail->exit_date);

            if ($today->gte($exitDate)) { // $today >= $exitDate
                return 'Your account access has ended as per your exit date (' . $exitDate->format('d/m/Y') . '). Please contact HR.';
            }

            // If today < exit_date but still can't login
            if ($employeeStatus === 'Inactive') {
                return 'Your account is marked as Inactive but you can still login until your exit date (' . $exitDate->format('d/m/Y') . ').';
            }
        }

        // Status based messages
        if ($employeeStatus === 'Inactive') {
            return 'Your account is inactive. Please contact administrator.';
        }

        return 'Your account is not active or login is not allowed.';
    }

    public function getCanLoginAttribute()
    {
        return $this->canLogin();
    }

    public function hasExitDatePassed()
    {
        if (!$this->employeeDetail || !$this->employeeDetail->exit_date) {
            return false;
        }

        $today = Carbon::today();
        $exitDate = Carbon::parse($this->employeeDetail->exit_date);

        return $today->gte($exitDate); // $today >= $exitDate
    }

    /**
     * Get current fiscal year
     */
    public function getCurrentFiscalYear()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Assuming fiscal year starts in April (04)
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
}
