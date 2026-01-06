@extends('admin.layout.app')

@section('content')
<div class="container-fluid">

    <div class="mb-3">
        <h4 class="fw-bold mb-0">Lead Contacts</h4>
        <small class="text-muted">Home / Lead Contacts</small>
    </div>



    <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">

        <div class="d-flex gap-2">
            <input type="date" class="form-control">
            <input type="date" class="form-control">

            <select class="form-select">
                <option>All</option>
            </select>

            <input type="text"
                   class="form-control"
                   placeholder="Start typing to search">
        </div>

        <button id="openFilter" class="btn btn-light">
            <i class="bx bx-filter"></i> Filters
        </button>

    </div>
</div>


<div class="d-flex align-items-center gap-2 mb-3">

    <button class="btn btn-primary">
        + Add Lead Contact
    </button>

    <button class="btn btn-outline-secondary">Lead Form</button>
    <button class="btn btn-outline-secondary">Import</button>
    <button class="btn btn-outline-secondary">Export</button>

    {{-- Hidden initially --}}
    <select id="bulkAction" class="form-select w-auto d-none">
        <option>No Action</option>
        <option>Delete</option>
    </select>

    <button id="applyBtn" class="btn btn-primary d-none">
        Apply
    </button>

</div>


<div class="card">
<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
    <th><input type="checkbox" id="checkAll"></th>
    <th>ID</th>
    <th>Contact Name</th>
    <th>Email</th>
    <th>Lead Owner</th>
    <th>Added By</th>
    <th>Created</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<tr>
    <td><input type="checkbox" class="rowCheck"></td>
    <td>44</td>
    <td>
        <strong>Dr. Pankaj Kumar</strong><br>
        <small class="text-muted">Hospital</small>
    </td>
    <td>test@gmail.com</td>
    <td>Mr Swarnendu Misra</td>
    <td>Miss Ankita Saha</td>
    <td>03-07-2025</td>
    <td>
        <button class="btn btn-light">⋮</button>
    </td>
</tr>
</tbody>

</table>
</div>
</div>

<script>
const checkAll = document.getElementById('checkAll');
const rowChecks = document.querySelectorAll('.rowCheck');
const bulkAction = document.getElementById('bulkAction');
const applyBtn = document.getElementById('applyBtn');

function toggleBulkUI() {
    let checked = [...rowChecks].some(c => c.checked);
    bulkAction.classList.toggle('d-none', !checked);
    applyBtn.classList.toggle('d-none', !checked);
}

checkAll.addEventListener('change', () => {
    rowChecks.forEach(c => c.checked = checkAll.checked);
    toggleBulkUI();
});

rowChecks.forEach(c => {
    c.addEventListener('change', toggleBulkUI);
});
</script>
