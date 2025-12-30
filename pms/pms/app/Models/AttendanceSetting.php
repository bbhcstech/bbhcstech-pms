<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
  protected $fillable = ['office_start_time', 'late_time'];
}
