<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LeadContact extends Model
{
    protected $fillable = [
        // Contact Information
        'salutation',
        'contact_name',
        'email',
        'mobile',

        // Company Information
        'company_name',
        'website',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'industry',

        // Lead Source & Status
        'lead_source',
        'status',
        'lead_score',
        'tags',

        // Assignment
        'lead_owner_id',
        'added_by',
        'lead_owner_designation',
        'added_by_designation',

        // Deal Information
        'create_deal',
        'deal_name',
        'deal_value',
        'deal_currency',
        'deal_agent_id',
        'pipeline',
        'deal_stage',
        'deal_category',
        'close_date',
        'products',

        // Additional Information
        'description',
    ];

    // Cast the JSON fields to arrays
    protected $casts = [
        'products' => 'array',
        'close_date' => 'date',
        'create_deal' => 'boolean',
        'deal_value' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'lead_owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Add relationship for deal agent
    public function dealAgent()
    {
        return $this->belongsTo(User::class, 'deal_agent_id');
    }

    // Add accessor for formatted deal value
    public function getFormattedDealValueAttribute()
    {
        if (!$this->deal_value) {
            return null;
        }

        $currencies = [
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $currencies[$this->deal_currency] ?? $this->deal_currency;
        return $symbol . number_format($this->deal_value, 2);
    }

    // Add accessor for formatted lead score
    public function getLeadScoreColorAttribute()
    {
        if ($this->lead_score >= 75) {
            return 'success'; // Green
        } elseif ($this->lead_score >= 50) {
            return 'warning'; // Yellow
        } elseif ($this->lead_score >= 25) {
            return 'info'; // Blue
        } else {
            return 'danger'; // Red
        }
    }

    // Add accessor for tags array
    public function getTagsArrayAttribute()
    {
        if (!$this->tags) {
            return [];
        }

        return array_map('trim', explode(',', $this->tags));
    }

    // Add method to check if deal exists
    public function hasDeal()
    {
        return $this->create_deal && $this->deal_name && $this->deal_value;
    }
}
