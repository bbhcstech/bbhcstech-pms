<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'due_date',
        'start_date',
        'status',
        'assigned_to',
        'added_by',
        'last_updated_by',
        'description',
        'files'
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
    
    
}
