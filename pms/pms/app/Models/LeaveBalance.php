<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'allocated_leaves',
        'used_leaves',
        'remaining_leaves',
        'carried_forward',
        'total_amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
