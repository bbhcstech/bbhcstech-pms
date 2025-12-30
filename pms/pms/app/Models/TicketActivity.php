<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketActivity extends Model
{
    protected $table = 'ticket_activities';

    protected $fillable = [
        'ticket_id',
        'project_id',
        'user_id',
        'assigned_to',
        'channel_id',
        'group_id',
        'type_id',
        'status',
        'priority',
        'type',
        'content',
    ];
}
