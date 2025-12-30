<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNote extends Model
{
    protected $fillable = [
        'project_id', 'title', 'type','employee_id', 'client_id', 'is_client_show',
        'ask_password', 'details', 'added_by', 'last_updated_by'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
    
    // app/Models/ProjectNote.php

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

}
