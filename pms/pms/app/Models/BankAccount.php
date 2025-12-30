<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'company_id',
        'type',
        'bank_name',
        'account_name',
        'account_number',
        'account_type',
        'currency_id',
        'contact_number',
        'opening_balance',
        'bank_logo',
        'status',
        'added_by',
        'last_updated_by',
        'bank_balance',
    ];

    /**
     * Relationships (optional depending on usage)
     */
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

   
}