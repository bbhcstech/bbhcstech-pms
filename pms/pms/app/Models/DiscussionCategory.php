<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'order',
        'name',
        'color',
    ];

    // Optional: If discussions table has `discussion_category_id`
    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }
}
