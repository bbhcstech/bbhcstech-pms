<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- correct import

class StickyNote extends Model
{
    
 protected $fillable = ['user_id', 'note_text', 'colour'];

}