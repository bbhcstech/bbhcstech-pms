<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'requester_id',
        'requester_type',
        'requester_name',
        'group_id',
        'agent_id',
        'project_id',
        'type',
        'subject',
        'description',
        'attachment',
        'priority',
        'channel',
        'tags',
        'status'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function group()
    {
        return $this->belongsTo(TicketGroup::class, 'group_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id'); // or morphTo() if polymorphic
    }

    public function replies()
{
    return $this->hasMany(Reply::class);
}

}
