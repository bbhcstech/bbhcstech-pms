<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{
    use SoftDeletes;
    
    protected $dates = ['last_reply_at'];

    protected $fillable = [
        'company_id',
        'discussion_category_id',
        'project_id',
        'title',
        'color',
        'user_id',
        'pinned',
        'closed',
        'best_answer_id',
        'last_reply_by_id',
        'added_by',
        'last_updated_by',
        'last_reply_at'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(DiscussionCategory::class, 'discussion_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(DiscussionReply::class);
    }
    
    // Discussion.php
    public function files()
    {
        return $this->hasMany(DiscussionFile::class);
    }
}
