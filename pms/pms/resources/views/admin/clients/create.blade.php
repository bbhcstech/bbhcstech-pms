@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <h5>Add Client</h5>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
            
    <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Account Details --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Account Details</div>
            &nbsp;
            <div class="card-body row g-3">

                {{-- Readonly auto-generated Client ID preview --}}
                <div class="col-md-3">
                    <label>Client ID</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $nextClientCode ?? '' }}"
                           readonly>
                </div>

                <div class="col-md-3">
                    <label>Salutation</label>
                    <select name="salutation" class="form-control">
                        <option value="">Select salutation</option>
                        <option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Miss">Miss</option>
                        <option value="Dr">Dr</option>
                        <option value="Sir">Sir</option>
                        <option value="Madam">Madam</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Client Name <sup class="text-danger">*</sup></label>
                    <input name="name" type="text" class="form-control" placeholder="e.g. John Doe" required>
                </div>

                <div class="col-md-3">
                    <label>Email <sup class="text-danger">*</sup></label>
                    <input name="email" required type="email" class="form-control" placeholder="e.g. johndoe@example.com">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="password">Password <sup class="text-danger">*</sup></label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" required class="form-control" autocomplete="off" minlength="8">
                        
                        <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password">
                            <i class="fa fa-eye"></i>
                        </button>
                
                        <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password">
                            <i class="fa fa-random"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Must have at least 9 characters</small>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Country <sup class="text-danger">*</sup></label>
                    <!-- Country dropdown -->
                    <select name="country" id="country" class="form-select form-select-sm select2">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" 
                                    data-flag="{{ $country->flag_url }}">
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Mobile <sup class="text-danger">*</sup></label>
                    <input name="mobile" type="text" class="form-control" placeholder="e.g. 1234567890" required>
                </div>

                <div class="col-md-4">
                    <label>Profile Picture</label>
                    <input name="profile_picture" type="file" class="form-control">
                </div>

                <div class="col-md-4">
                    <label>Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Change Language</label>
                    <select name="language" id="language" class="form-select form-select-sm select2">
                        <option value="en" data-flag="https://flagcdn.com/w20/gb.png">English</option>
                        <option value="bn" data-flag="https://flagcdn.com/w20/bd.png">Bengali</option>
                        <option value="hi" data-flag="https://flagcdn.com/w20/in.png">Hindi</option>
                        <option value="fr" data-flag="https://flagcdn.com/w20/fr.png">French</option>
                        <option value="de" data-flag="https://flagcdn.com/w20/de.png">German</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Client Category</label>
                    <div class="input-group">
                        <select name="client_category_id" id="client_category_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+</button>
                    </div>
                </div>
            
                <div class="col-md-6">
                    <label>Client Sub Category</label>
                    <div class="input-group">
                        <select name="client_sub_category_id" id="client_sub_category_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($subcategories as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addSubCategoryModal">+</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label>Login Allowed?  <sup class="text-danger">*</sup></label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="login_allowed" value="1" checked>
                        <label class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="login_allowed" value="0">
                        <label class="form-check-label">No</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label>Receive Email Notifications?</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="email_notifications" value="1" checked>
                        <label class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="email_notifications" value="0">
                        <label class="form-check-label">No</label>
                    </div>
                </div>

            </div>
        </div>

        {{-- Company Details --}}
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">Company Details</div>
            &nbsp;
            <div class="card-body row g-3">

                <div class="col-md-6">
                    <label>Company Name</label>
                    <input name="company_name" type="text" class="form-control" placeholder="e.g. Acme Corporation">
                </div>

                <div class="col-md-6">
                    <label>Official Website</label>
                    <input name="website" type="url" class="form-control" placeholder="https://www.example.com">
                </div>

                <div class="col-md-4">
                    <label>Tax Name</label>
                    <input name="tax_name" type="text" class="form-control" placeholder="e.g. GST/VAT">
                </div>

                <div class="col-md-4">
                    <label>GST/VAT Number</label>
                    <input name="tax_number" type="text" class="form-control" placeholder="e.g. 18AABCU960XXXXX">
                </div>

                <div class="col-md-4">
                    <label>Office Phone</label>
                    <input name="office_phone" type="text" class="form-control" id="office_phone" placeholder="+91XXXXXXXXXX">
                </div>

                <div class="col-md-4">
                    <label>City</label>
                    <input name="city" type="text" class="form-control" placeholder="e.g. New York">
                </div>

                <div class="col-md-4">
                    <label>State</label>
                    <input name="state" type="text" class="form-control" placeholder="e.g. California">
                </div>

                <div class="col-md-4">
                    <label>Postal Code</label>
                    <input name="postal_code" type="text" class="form-control" placeholder="e.g. 90250">
                </div>

                <div class="col-md-12">
                    <label>Company Address</label>
                    <textarea name="company_address" class="form-control" rows="2" placeholder="e.g. 132, My Street, Kingston, NY"></textarea>
                </div>

                <div class="col-md-12">
                    <label>Shipping Address</label>
                    <textarea name="shipping_address" class="form-control" rows="2" placeholder="e.g. 132, My Street, Kingston, NY"></textarea>
                </div>

                <div class="col-md-12">
                    <label>Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>

                <div class="col-md-6">
                    <label>Company Logo</label>
                    <input name="company_logo" type="file" class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Added By</label>
                    <select name="added_by" class="form-control">
                        <option value="">Select</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
        
        <div class="mb-3 text-end">
            <button class="btn btn-success">Save Client</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </form>
    
    <!-- Add Client Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addCategoryForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Add Category</h5></div>
                    <div class="modal-body">
                        <input type="text" name="name" id="categoryName" class="form-control" placeholder="Enter category name" autocomplete="off">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Client Sub-Category Modal -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addSubCategoryForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Add Sub Category</h5></div>
                    <div class="modal-body">
                        <input type="text" name="name" id="subcategoryName" class="form-control mb-2" placeholder="Sub Category name">
                        <select name="client_category_id" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- Bootstrap-select CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" />

<!-- Bootstrap-select JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Show/Hide Password
        document.querySelector('.toggle-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Generate Random Password
        document.querySelector('.generate-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const randomPassword = Math.random().toString(36).slice(-10) + '!A1';
            passwordField.value = randomPassword;
        });
    });
</script>

<script>
setTimeout(() => {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
}, 500);

$('#addCategoryModal').on('shown.bs.modal', function () {
    $('#categoryName').focus();
});

$('#addSubCategoryModal').on('shown.bs.modal', function () {
    $('#subcategoryName').focus();
});

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();

        const name = $('#categoryName').val().trim();
        if (name === '') {
            alert('Please enter a category name');
            return;
        }

        const form = $(this);
        const formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('client-categories.store') }}",
            data: formData,
            success: function(data) {
                $('#addCategoryModal').modal('hide');
                form[0].reset();

                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON.errors.name) {
                    alert(xhr.responseJSON.errors.name[0]);
                } else {
                    alert('Error: ' + (xhr.responseJSON.message || 'Validation failed'));
                }
            }
        });
    });

    // Add Sub-Category
    $('#addSubCategoryForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('client-sub-categories.store') }}",
            data: formData,
            success: function(data) {
                $('#client_sub_category_id').append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
            
                $('#addSubCategoryModal').modal('hide');
                form[0].reset();
            
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON.message || 'Validation failed'));
            }
        });
    });
});
</script>

<script>
$(document).ready(function () {
    function formatOption (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    // Country dropdown
    $('#country').select2({
        theme: "bootstrap-5",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Country",
        allowClear: true
    });

    // Language dropdown
    $('#language').select2({
        theme: "bootstrap-5",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Language",
        allowClear: true
    });
});
</script>

<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select members",
        width: '100%'
    });
});
</script>

<script>
// Auto-prefix +91 for office phone when user focuses
document.addEventListener("DOMContentLoaded", function () {
    const phoneInput = document.getElementById("office_phone");
    if (!phoneInput) return;

    phoneInput.addEventListener("focus", function () {
        if (!phoneInput.value.startsWith("+91")) {
            phoneInput.value = "+91";
        }
    });
});
</script>

@endsection
