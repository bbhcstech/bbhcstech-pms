<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $table = 'project_user'; // since it's not plural

    protected $fillable = [
        'project_id',
        'user_id',
        'hourly_rate',
        'role',
    ];

    public $timestamps = true; // if your table has timestamps

    // Relationships (optional if you want to directly use them)
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('hourly_rate', 'role')
            ->withTimestamps();
    }


    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('hourly_rate', 'role')
            ->withTimestamps();
    }

}
