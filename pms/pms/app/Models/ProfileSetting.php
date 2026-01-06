<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileSetting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'type',
        'options',
        'required',
        'visible',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'visible' => 'boolean',
    ];
}
