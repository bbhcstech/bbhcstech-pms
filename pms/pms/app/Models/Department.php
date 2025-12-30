<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'dpt_name',
        'dpt_code',
        'parent_dpt_id',
        'added_by',
        'last_updated_by'
    ];

    // Parent department (if any)
    public function parent()
{
    return $this->belongsTo(ParentDepartment::class, 'parent_dpt_id');
}


    // All sub departments under this department
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_dpt_id');
    }

    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class, 'department_id');
    }
}
