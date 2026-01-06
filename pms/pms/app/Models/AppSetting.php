<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'label',
        'description',
        'value',
        'type',
        'options',
        'min_value',
        'max_value',
        'unit',
        'placeholder',
        'section',
        'page',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
