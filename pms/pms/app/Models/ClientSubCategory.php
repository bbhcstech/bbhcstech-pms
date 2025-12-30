<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSubCategory extends Model
{
    protected $fillable = ['name', 'client_category_id'];
    public function category()
    {
        return $this->belongsTo(ClientCategory::class);
    }
}
