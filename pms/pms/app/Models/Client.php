<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'salutation',
        'name',
        'email',
        'company_name',
        'password',
        'country',
        'mobile',
        'profile_picture',
        'gender',
        'language',
        'client_category_id',
        'client_sub_category_id',
        'login_allowed',
        'email_notifications',
        'website',
        'tax_name',
        'tax_number',
        'office_phone',
        'city',
        'state',
        'postal_code',
        'added_by',
        'company_address',
        'shipping_address',
        'note',
        'company_logo',
        'status',
        'client_uid',
    ];

    protected $attributes = [
        'status' => 'active',
        'login_allowed' => 1,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            $latest = static::orderBy('id', 'desc')->first();
            $number = $latest ? $latest->id + 1 : 1;

            $client->client_uid = 'XINK-CL-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }
}

