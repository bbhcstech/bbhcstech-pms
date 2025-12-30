<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
     protected $fillable = ['user_id','award_id', 'title', 'description', 'award_date','image','status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
     public function appreciation()
    {
        return $this->belongsTo(Appreciations::class, 'award_id');
    }
}
