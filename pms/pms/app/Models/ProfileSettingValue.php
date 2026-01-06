<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileSettingValue extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];
}
