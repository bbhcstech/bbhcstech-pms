<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCategory extends Model
{
    protected $fillable = ['name'];
    public function subCategories()
    {
        return $this->hasMany(ClientSubCategory::class);
    }
}
