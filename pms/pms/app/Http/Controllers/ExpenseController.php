<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\User;
use App\Models\Project;
use App\Models\ExpenseCategory;
use App\Models\BankAccount;
use App\Models\Currency;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index($projectId)
{
    $employees  = User::all();
    $project = Project::findOrFail($projectId); // renamed from $projects to $project

    $categories = ExpenseCategory::all();
    $accounts = BankAccount::all();
    $currency = Currency::all();

    $expenses = Expense::where('project_id', $projectId)
                ->with(['employee', 'project', 'category', 'bankAccount'])
                ->latest()->get();

    return view('admin.expenses.index', compact('expenses','employees','project','categories','accounts','currency', 'projectId'));
}



    public function edit($projectId, $id)
{
    $expense = Expense::findOrFail($id);
    $employees = User::all();
    $projects = Project::all();
    $categories = ExpenseCategory::all();
    $accounts = BankAccount::all();

    return view('admin.expenses.edit', compact('expense', 'employees', 'projects', 'categories', 'accounts', 'projectId'));
}

public function update(Request $request, $projectId, $id)
{
    $request->validate([
        'item_name' => 'required|string',
        'currency' => 'required|string',
        'exchange_rate' => 'required|numeric',
        'price' => 'required|numeric',
        'purchase_date' => 'required|date',
        'employee_id' => 'nullable|exists:users,id',
        'project_id' => 'nullable|exists:projects,id',
        'category_id' => 'nullable|exists:expenses_category,id',
        'bank_account_id' => 'nullable|exists:bank_accounts,id',
        'bill' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    ]);

    $expense = Expense::findOrFail($id);
    $data = $request->except('bill');

    if ($request->hasFile('bill')) {
        if ($expense->bill && file_exists(public_path($expense->bill))) {
            unlink(public_path($expense->bill));
        }

        $bill = $request->file('bill');
        $billName = time() . '-' . $bill->getClientOriginalName();
        $bill->move(public_path('admin/uploads/expenses'), $billName);
        $data['bill'] = 'admin/uploads/expenses/' . $billName;
    }

    $expense->update($data);

    return redirect()->route('expenses.index', $projectId)->with('success', 'Expense updated successfully.');
}


   public function destroy($projectId, $id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->bill && Storage::disk('public')->exists($expense->bill)) {
            Storage::disk('public')->delete($expense->bill);
        }
        $expense->delete();
        return redirect()->back()->with('success', 'Expense deleted.');
    }

    
   public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string',
            'currency' => 'required|string',
            'exchange_rate' => 'required|numeric',
            'price' => 'required|numeric',
            'purchase_date' => 'required|date',
            'employee_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'nullable|exists:expenses_category,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bill' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);
    
        $data = $request->all();
    
       
        
        $data['bill'] = null;

            // Handle profile image upload
            if ($request->hasFile('bill')) {
                $bill = $request->file('bill');
                $billName = time() . '-' . $bill->getClientOriginalName();
                $bill->move(public_path('admin/uploads/expenses'), $billName);
        
                $data['bill'] = 'admin/uploads/expenses/' . $billName;
            }
    
        Expense::create($data);
    
        return redirect()->back()->with('success', 'Expense added successfully.');
    }

}
