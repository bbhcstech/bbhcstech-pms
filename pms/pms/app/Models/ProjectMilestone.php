<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id', 'title', 'cost', 'status', 'add_to_budget',
        'summary', 'start_date', 'end_date'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}