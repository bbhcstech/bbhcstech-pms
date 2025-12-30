<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ParentDepartment;

class CompanyAddress extends Model
{
   protected $fillable = ['country_id', 'address', 'is_default', 'location', 'tax_number', 'tax_name', 'longitude', 'latitude'];

    public static function defaultAddress()
    {
        return CompanyAddress::where('is_default', 1)->first();
    }
    
}