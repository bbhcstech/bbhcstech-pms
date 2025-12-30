<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    protected $table = 'project_category'; // 👈 match your DB table name
    protected $fillable = ['category_name'];
}

