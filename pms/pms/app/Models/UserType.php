<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserType extends Model
{
    protected $table = 'user_type';
    protected $fillable = ['type'];

    public function clinic()
    {
        return $this->hasOne(Client::class, 'user_id', 'id');
    }
    public function doctor()
    {
        return $this->hasOne(DoctorList::class, 'user_id', 'id');
    }
}