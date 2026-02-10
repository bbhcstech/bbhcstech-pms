<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'annual_leaves',
        'pro_rate_enabled',
        'fiscal_year_start',
        'fiscal_year_end',
        'allow_carry_forward',
        'max_carry_forward',
        'leave_monetary_value'
    ];

    protected $casts = [
        'pro_rate_enabled' => 'boolean',
        'allow_carry_forward' => 'boolean',
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
    ];
}
