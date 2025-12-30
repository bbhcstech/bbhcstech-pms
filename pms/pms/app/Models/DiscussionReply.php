<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionReply extends Model
{
    use SoftDeletes;

    protected $table = 'discussion_replies';

    protected $fillable = [
        'company_id',
        'discussion_id',
        'user_id',
        'body',
    ];

    /**
     * Relationship: The discussion this reply belongs to
     */
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    /**
     * Relationship: The user who replied
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Files attached to this reply
     */
    public function files()
    {
        return $this->hasMany(DiscussionFile::class, 'discussion_reply_id');
    }
}
