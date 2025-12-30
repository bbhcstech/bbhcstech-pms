<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectActivity extends Model
{
    use HasFactory;

    protected $table = 'project_activity';

    protected $fillable = [
        'project_id',
        'activity',
    ];

    /**
     * Relationship: Activity belongs to a project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
