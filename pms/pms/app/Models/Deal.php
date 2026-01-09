<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // 'lead_id',          // Add this - foreign key to leads table
        'deal_name',
        'lead_name',        // Keep for backward compatibility
        'contact_details',  // Keep for backward compatibility
        'value',
        'close_date',
        'next_follow_up',
        'deal_agent_id',
        'deal_stage_id',
        'deal_category_id',
        'pipeline',
        'product',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'close_date' => 'date',
        'next_follow_up' => 'date',
        'is_active' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'deal_agent_id');
    }

    public function stage()
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function category()
    {
        return $this->belongsTo(DealCategory::class, 'deal_category_id');
    }

    // Watchers relationship
    public function watchers()
    {
        return $this->belongsToMany(User::class, 'deal_watchers', 'deal_id', 'user_id')
                    ->withTimestamps();
    }

    // ADD THIS: Lead relationship
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    // Remove all the extra methods for now to keep it clean
    // We'll add them back later if needed
}
