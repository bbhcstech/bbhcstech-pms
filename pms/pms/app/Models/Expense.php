<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'item_name', 'currency', 'exchange_rate', 'price', 'purchase_date',
        'employee_id', 'project_id', 'category_id', 'purchased_from',
        'bank_account_id', 'description', 'bill'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function category() {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function bankAccount() {
        return $this->belongsTo(BankAccount::class);
    }
}
