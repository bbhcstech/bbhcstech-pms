@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <h5>Edit Client</h5>
    
    
    @if(session('error'))
    <div class="alert alert-danger" style="background-color: #dc3545; color: white; border-color: #dc3545;">
        {{ session('error') }}
    </div>
@endif
            
    <form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Account Details --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Account Details</div>
            &nbsp;
            <div class="card-body row g-3">

                <div class="col-md-3">
                    <label>Salutation</label>
                    <select name="salutation" class="form-control">
                        <option value="">Select salutation</option>
                        @foreach(['Mr', 'Mrs', 'Miss', 'Dr', 'Sir', 'Madam'] as $salute)
                            <option value="{{ $salute }}" {{ $client->salutation == $salute ? 'selected' : '' }}>{{ $salute }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Client Name <sup class="text-danger">*</sup></label>
                    <input name="name" type="text" class="form-control" value="{{ $client->name }}" required>
                </div>

                <div class="col-md-3">
                    <label>Email <sup class="text-danger">*</sup></label>
                    <input name="email" required type="email" class="form-control" value="{{ $client->email }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="password">Password <sup class="text-danger">*</sup></label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="off" minlength="8">
                        <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password">
                            <i class="fa fa-random"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Leave blank to keep current password</small>
                </div>

                <div class="col-md-4">
                    <label>Country <sup class="text-danger">*</sup></label>
                   <select name="country" id="country" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" {{ $client->country == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Mobile <sup class="text-danger">*</sup></label>
                    <input name="mobile" type="text" class="form-control" value="{{ $client->mobile }}" required>
                </div>

                <div class="col-md-4">
                    <label>Profile Picture</label>
                    <input name="profile_picture" type="file" class="form-control">
                    @if($client->profile_picture)
                        <small>Current: <a href="{{ asset($client->profile_picture) }}" target="_blank">View</a></small>
                    @endif
                </div>

                <div class="col-md-4">
                    <label>Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Male', 'Female', 'Other'] as $gender)
                            <option {{ $client->gender == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>

                <!--<div class="col-md-4">-->
                <!--    <label>Change Language</label>-->
                <!--    <select name="language" id="language" class="form-select form-select-sm select2">-->
                <!--        @foreach(['en'=>'English', 'bn'=>'Bengali', 'hi'=>'Hindi', 'fr'=>'French', 'de'=>'German'] as $code => $lang)-->
                <!--            <option value="{{ $code }}" {{ $client->language == $code ? 'selected' : '' }}>{{ $lang }}</option>-->
                <!--        @endforeach-->
                <!--    </select>-->
                <!--</div>-->
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Change Language</label>
                    <select name="language" id="language" class="form-select form-select-sm select2">
                        <option value="en" data-flag="https://flagcdn.com/w20/gb.png" {{ $client->language == 'en' ? 'selected' : '' }}>English</option>
                        <option value="bn" data-flag="https://flagcdn.com/w20/bd.png" {{ $client->language == 'bn' ? 'selected' : '' }}>Bengali</option>
                        <option value="hi" data-flag="https://flagcdn.com/w20/in.png" {{ $client->language == 'hi' ? 'selected' : '' }}>Hindi</option>
                        <option value="fr" data-flag="https://flagcdn.com/w20/fr.png" {{ $client->language == 'fr' ? 'selected' : '' }}>French</option>
                        <option value="de" data-flag="https://flagcdn.com/w20/de.png" {{ $client->language == 'de' ? 'selected' : '' }}>German</option>
                    </select>
                </div>


                <div class="col-md-6">
                    <label>Client Category</label>
                    <select name="client_category_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $client->client_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Client Sub Category</label>
                    <select name="client_sub_category_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ $client->client_sub_category_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Login Allowed?  <sup class="text-danger">*</sup></label><br>
                    <input type="radio" name="login_allowed" value="1" {{ $client->login_allowed ? 'checked' : '' }}> Yes
                    <input type="radio" name="login_allowed" value="0" {{ !$client->login_allowed ? 'checked' : '' }}> No
                </div>

                <div class="col-md-6">
                    <label>Receive Email Notifications?</label><br>
                    <input type="radio" name="email_notifications" value="1" {{ $client->email_notifications ? 'checked' : '' }}> Yes
                    <input type="radio" name="email_notifications" value="0" {{ !$client->email_notifications ? 'checked' : '' }}> No
                </div>

            </div>
        </div>

        {{-- Company Details --}}
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">Company Details</div>
            &nbsp;
            <div class="card-body row g-3">
                @php
                    $fields = ['company_name', 'website', 'tax_name', 'tax_number', 'office_phone', 'city', 'state', 'postal_code', 'company_address', 'shipping_address', 'note'];
                @endphp
                @foreach($fields as $field)
                    @if(in_array($field, ['company_address', 'shipping_address', 'note']))
                        <div class="col-md-12">
                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                            <textarea name="{{ $field }}" class="form-control" rows="2">{{ $client->$field }}</textarea>
                        </div>
                    @else
                        <div class="col-md-4">
                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                            <input name="{{ $field }}" type="text" class="form-control" value="{{ $client->$field }}">
                        </div>
                    @endif
                @endforeach

                <div class="col-md-6">
                    <label>Company Logo</label>
                    <input name="company_logo" type="file" class="form-control">
                    &nbsp;
                    @if($client->company_logo)
                    <div class="border p-2 rounded bg-light" style="max-width: 150px;">
                        <img src="{{ asset($client->company_logo) }}" alt="Company Logo" class="img-fluid rounded" style="max-height: 100px;">
                        <div>
                            <small class="text-muted">Current Logo</small>
                        </div>
                    </div>
                @endif
                </div>

                <div class="col-md-6">
                    <label>Added By</label>
                    <select name="added_by" class="form-control">
                        <option value="">Select</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $client->added_by == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3 text-end">
            <button class="btn btn-primary">Update Client</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </form>
</div>
<!-- ✅ jQuery First -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Optional: Bootstrap JS (after jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Then your custom script -->
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
            const randomPassword = Math.random().toString(36).slice(-10) + '!A1'; // ensure complexity
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

            // Clean backdrop in case it gets stuck
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON.errors.name) {
                alert(xhr.responseJSON.errors.name[0]);
            } else {
                alert('Error: ' + xhr.responseJSON.message || 'Validation failed');
            }
        }
    });
});

   
    // ✅ Add Sub-Category
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
            
                // ✅ Clean stuck backdrop (just like category modal)
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            },
                        error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message || 'Validation failed');
            }
        });
    });

});
</script>

<script>
// $(document).ready(function () {
//     function formatCountry (state) {
//         if (!state.id) return state.text; // default option
//         let flag = $(state.element).data("flag");
//         if (flag) {
//             return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
//         }
//         return state.text;
//     }

//     $('#country').select2({
//         theme: "bootstrap-5",   // ✅ Bootstrap 5 theme
//         templateResult: formatCountry,
//         templateSelection: formatCountry,
//         placeholder: "Select Country",
//         allowClear: true
//     });
    
    
// });


$(document).ready(function () {
    function formatOption (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    // ✅ Country dropdown
    $('#country').select2({
        theme: "bootstrap-5",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Country",
        allowClear: true
    });

    // ✅ Language dropdown (with search enabled)
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
// $(document).ready(function() {
//     function formatFlag(option) {
//         if (!option.id) { return option.text; }
//         var flagUrl = $(option.element).data('flag');
//         if (!flagUrl) { return option.text; }
//         return $('<span><img src="' + flagUrl + '" class="me-2" style="width:18px;"/> ' + option.text + '</span>');
//     }

//     $('#language').select2({
//         templateResult: formatFlag,
//         templateSelection: formatFlag,
//         minimumResultsForSearch: -1 // hides search box if not needed
//     });
// });
</script>


@endsection
