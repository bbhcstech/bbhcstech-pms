<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dpt_name',
        'dpt_code',
        'added_by',
        'last_updated_by',
    ];

    // All sub departments under this parent
    public function departments()
    {
        return $this->hasMany(Department::class, 'parent_dpt_id');
    }

    // All employees assigned to this parent department
    public function employees()
    {
        return $this->hasMany(EmployeeDetail::class, 'parent_dpt_id');
    }
}
