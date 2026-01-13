<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appreciations extends Model
{
     protected $fillable = ['title', 'summary', 'status','icon','color_code'];


    // public function awards()
    // {
    //     return $this->hasMany(Award::class, 'award_id');
    // }

    public function awards()
{
    return $this->hasMany(Award::class, 'appreciation_id'); // Correct foreign key
}
}
