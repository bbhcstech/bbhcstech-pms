<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',             // DB column name
        'name',
        'parent_id',
        'unique_code',
        'added_by',
        'last_updated_by',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate unique_code after creation
        static::created(function ($model) {
            if (empty($model->unique_code)) {
                $model->unique_code = 'DGN-' . str_pad($model->id, 4, '0', STR_PAD_LEFT);
                $model->saveQuietly();
            }
        });
    }

    // RELATIONSHIPS
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class, 'designation_id');
    }

    public function parent()
    {
        return $this->belongsTo(Designation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Designation::class, 'parent_id');
    }
}
