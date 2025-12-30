<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $table = 'expenses_category'; // explicitly set table name

    protected $fillable = [
        'company_id',
        'category_name',
        'added_by',
        'last_updated_by',
    ];

    /**
     * Optional: Relationship if expenses use category_id
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

