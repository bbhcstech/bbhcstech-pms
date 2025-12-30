<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'discussion_id',
        'discussion_reply_id',
        'filename',
        'description',
        'google_url',
        'hashname',
        'size',
        'dropbox_link',
        'external_link_name',
    ];

    /**
     * Get the user who uploaded the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the discussion this file is attached to.
     */
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    /**
     * Get the reply (if any) this file is attached to.
     */
    public function discussionReply()
    {
        return $this->belongsTo(DiscussionReply::class);
    }
}
