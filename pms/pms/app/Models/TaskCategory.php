<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCategory extends Model
{
    use HasFactory;

    protected $table = 'task_category'; // Explicitly mention the table name
    protected $fillable = [
        'company_id',
        'category_name',
        'added_by',
        'last_updated_by',
    ];
    
    public function tasks()
{
    return $this->hasMany(Task::class, 'category_id');
}

}
