<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $fillable = [
     
        'currency_name',
        'currency_symbol',
        'currency_code',
        'exchange_rate',
        'is_cryptocurrency',
        'usd_price',
        'currency_position',
        'no_of_decimal',
        'thousand_separator',
        'decimal_separator',
    ];

    /**
     * Relationships (if used)
     */
 
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
}
