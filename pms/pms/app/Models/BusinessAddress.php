<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAddress extends Model
{
    use HasFactory;

    protected $table = 'business_addresses'; // safe & explicit

    protected $fillable = [
        'location',
        'address',
        'country',
        'tax_name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
