<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLabel extends Model
{
   use HasFactory;

    protected $table = 'task_label_list';

    protected $fillable = [
        'project_id',
        'label_name',
        'color',
        'description',
        'task_id'
    ];

    // Relationships

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }
    
     public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
