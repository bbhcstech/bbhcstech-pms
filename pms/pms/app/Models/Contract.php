<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'subject',
        'client_id',
        'project_id',
        'description',
        'type',
        'contract_value',
        'currency',
        'start_date',
        'end_date',
        'status',
        'terms',
        'notes',
        'is_signed',
        'signed_date',
        'signed_by',
        'created_by',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'is_signed' => 'boolean',
    ];

    /**
     * Get the client that owns the contract.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the project associated with the contract.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the contract.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active contracts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include draft contracts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include expired contracts.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                     ->orWhereDate('end_date', '<', now());
    }

    /**
     * Generate contract number.
     */
    public static function generateContractNumber()
    {
        $prefix = 'CONTR';
        $lastContract = self::latest()->first();

        if ($lastContract) {
            $lastNumber = (int) str_replace($prefix, '', $lastContract->contract_number);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Check if contract is expired.
     */
    public function isExpired()
    {
        return $this->end_date < now() || $this->status === 'expired';
    }

    /**
     * Check if contract is active.
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
