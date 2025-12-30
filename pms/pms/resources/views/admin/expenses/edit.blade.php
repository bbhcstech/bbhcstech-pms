@extends('admin.layout.app')

@section('content')
<div class="container">
    <h4>Edit Expense</h4>
    <form action="{{ route('expenses.update', [$expense->project_id, $expense->id]) }}" method="POST" enctype="multipart/form-data">

        @csrf @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Item Name *</label>
                <input type="text" name="item_name" class="form-control" value="{{ $expense->item_name }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Currency</label>
                <select name="currency" class="form-select">
                    <option value="INR" {{ $expense->currency == 'INR' ? 'selected' : '' }}>Rupee</option>
                    <option value="USD" {{ $expense->currency == 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label>Exchange Rate *</label>
                <input type="number" name="exchange_rate" value="{{ $expense->exchange_rate }}" step="0.01" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Price *</label>
                <input type="number" name="price" value="{{ $expense->price }}" step="0.01" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Purchase Date *</label>
                <input type="date" name="purchase_date" value="{{ $expense->purchase_date }}" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $expense->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Project</label>
                <select name="project_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $expense->project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Expense Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $expense->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Purchased From</label>
                <input type="text" name="purchased_from" value="{{ $expense->purchased_from }}" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label>Bank Account</label>
                <select name="bank_account_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $expense->bank_account_id == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 mb-3">
                <label>Bill</label><br>
                @if($expense->bill)
                    <a href="{{ asset('/'.$expense->bill) }}" target="_blank">View Current</a><br>
                @endif
                <input type="file" name="bill" class="form-control mt-2">
            </div>

            <div class="col-md-12 mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2">{{ $expense->description }}</textarea>
            </div>

            <div class="col-md-12">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('expenses.index', $expense->project_id) }}" class="btn btn-secondary">Cancel</a>

            </div>
        </div>
    </form>
</div>
@endsection
