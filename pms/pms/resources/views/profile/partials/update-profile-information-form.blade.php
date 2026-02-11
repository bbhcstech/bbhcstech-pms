<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>


    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-4">
        @csrf
        @method('PATCH')

        <!-- Profile Picture -->
        <div class="col-md-12">
            <label class="form-label">Profile Picture</label>
            <div class="mb-3">
                @if ($user->profile_image)
                    <img src="{{ asset($user->profile_image) }}" alt="Profile" width="100" class="rounded mb-2">
                @endif
                <input type="file" name="profile_image" class="form-control">
            </div>
        </div>

        <!-- Name & Email -->
        <div class="col-md-6">
            <label class="form-label">Your Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Your Email *</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

     <!-- Designation -->
        <div class="col-md-6">
            <label class="form-label">Designation <span class="text-danger">*</span></label>
            <select name="designation" class="form-select @error('designation') is-invalid @enderror" required>
                <option value="">Select Designation</option>
                @foreach($designations as $designation)
                    <option value="{{ $designation->name }}"
                        {{ old('designation', $user->designation) == $designation->name ? 'selected' : '' }}>
                        {{ $designation->name }}
                    </option>
                @endforeach
            </select>
            @error('designation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if($designations->isEmpty())
                <div class="text-warning small mt-1">No designations found in database. Please add designations first.</div>
            @endif
        </div>



        <!-- Mobile -->
        <div class="col-md-6">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $user->mobile) }}">
        </div>

        <!-- Gender -->
        <div class="col-md-4">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <!-- DOB -->
        <div class="col-md-4">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="{{ old('dob', $user->dob) }}">
        </div>

        <!-- Marital Status -->
        <div class="col-md-4">
            <label class="form-label">Marital Status</label>
            <select name="marital_status" class="form-select">
                <option value="">Select</option>
                <option value="single" {{ old('marital_status', $user->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                <option value="married" {{ old('marital_status', $user->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
            </select>
        </div>

        <!-- Slack ID -->
        <div class="col-md-6">
            <label class="form-label">Slack Member ID</label>
            <input type="text" name="slack_id" class="form-control" placeholder="@" value="{{ old('slack_id', $user->slack_id) }}">
        </div>

        <!-- Language & Country -->
        <div class="col-md-3">
            <label class="form-label">Change Language</label>
            <select name="language" class="form-select">
                <option value="en" {{ $user->language == 'en' ? 'selected' : '' }}>English</option>
                <option value="bn" {{ $user->language == 'bn' ? 'selected' : '' }}>Bengali</option>
                <!-- add more -->
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Country</label>
            <input type="text" name="country" class="form-control" value="{{ old('country', $user->country) }}">
        </div>

        <!-- Address -->
        <div class="col-md-12">
            <label class="form-label">Your Address</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
        </div>

        <!-- About -->
        <div class="col-md-12">
            <label class="form-label">About</label>
            <textarea name="about" class="form-control" rows="3">{{ old('about', $user->about) }}</textarea>
        </div>

        <!-- Notifications -->
        <div class="col-md-6">
            <label class="form-label">Receive Email Notifications?</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="email_notify" value="1" {{ $user->email_notify ? 'checked' : '' }}>
                <label class="form-check-label">Enable</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="email_notify" value="0" {{ !$user->email_notify ? 'checked' : '' }}>
                <label class="form-check-label">Disable</label>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Enable Google Calendar</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="google_calendar" value="1" {{ $user->google_calendar ? 'checked' : '' }}>
                <label class="form-check-label">Yes</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="google_calendar" value="0" {{ !$user->google_calendar ? 'checked' : '' }}>
                <label class="form-check-label">No</label>
            </div>
        </div>

        <!-- Save -->
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</section>
