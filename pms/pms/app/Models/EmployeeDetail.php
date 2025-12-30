<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeDetail extends Model
{
    protected $fillable = [
        'user_id', 'designation_id', 'parent_dpt_id', 'department_id', 'employee_id',
        'salutation', 'country', 'mobile', 'gender', 'joining_date', 'dob', 'reporting_to',
        'language', 'user_role', 'address', 'about', 'login_allowed',
        'email_notifications', 'hourly_rate', 'slack_member_id', 'skills',
        'probation_end_date', 'notice_start_date', 'notice_end_date',
        'employment_type', 'marital_status', 'business_address', 'status', 'exit_date'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'dob' => 'date',
        'probation_end_date' => 'date',
        'notice_start_date' => 'date',
        'notice_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'login_allowed' => 'boolean',
        'email_notifications' => 'boolean',
        'hourly_rate' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function reportingTo()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    // **********************
    // COMPUTED STATUS LOGIC
    // **********************
    /**
     * Determine inferred employment status.
     *
     * Returns one of:
     *  - 'probation' when probation_end_date exists and today <= probation_end_date
     *  - 'notice' when notice_start_date or notice_end_date is present
     *  - 'permanent' otherwise
     *
     * @return string
     */
    public function getEmploymentStatusAttribute(): string
    {
        $today = Carbon::today();

        // If probation_end_date is set and not passed => probation
        if (!empty($this->probation_end_date)) {
            $probationEnd = $this->probation_end_date instanceof Carbon
                ? $this->probation_end_date
                : Carbon::parse($this->probation_end_date);

            if ($today->lte($probationEnd)) {
                return 'probation';
            }
        }

        // If notice period fields exist => notice
        if (!empty($this->notice_start_date) || !empty($this->notice_end_date)) {
            return 'notice';
        }

        return 'permanent';
    }
}
