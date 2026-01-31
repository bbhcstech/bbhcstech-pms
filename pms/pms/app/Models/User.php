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
}
