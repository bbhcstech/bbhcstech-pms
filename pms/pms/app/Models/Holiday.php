<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'title',
        'date',
        'occassion',
        'type', // Optional: could be 'holiday' or 'weekend'
        'recurring_day',
        'department_id_json',
        'designation_id_json',
        'employment_type_json',
        'notification_sent'
        
    ];

    protected $dates = ['date'];
    
    public function group()
{
    return $this->belongsTo(Holiday::class, 'group_id', 'group_id')
                ->with('holidays');
}

public function holidays()
{
    return $this->hasMany(Holiday::class, 'group_id', 'group_id');
}

}
