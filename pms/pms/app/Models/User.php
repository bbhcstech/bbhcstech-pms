<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'mobile',
        'designation',
        'gender',
        'dob',
        'marital_status',
        'address',
        'about',
        'country',
        'language',
        'slack_id',
        'email_notify',
        'google_calendar',
        'profile_image',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }
    
        public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }
    
    public function leaves()
    {
        return $this->hasMany(Leave::class, 'user_id');
    }
    
    
public function projects()
{
    return $this->belongsToMany(\App\Models\Project::class, 'project_user', 'user_id', 'project_id')
                ->withPivot('hourly_rate', 'role')
                ->withTimestamps();
}



}
