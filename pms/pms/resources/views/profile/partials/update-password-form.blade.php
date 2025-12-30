<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

   <form method="POST" action="{{ route('password.update') }}" class="row g-3 needs-validation" novalidate>
    @csrf
    @method('put')

   

    <!-- Current Password -->
    <div class="col-md-6">
        <label for="update_password_current_password" class="form-label">Current Password</label>
        <input type="password" class="form-control @error('updatePassword.current_password') is-invalid @enderror"
               id="update_password_current_password" name="current_password" autocomplete="current-password">
        @error('updatePassword.current_password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="col-md-6">
        <label for="update_password_password" class="form-label">New Password</label>
        <input type="password" class="form-control @error('updatePassword.password') is-invalid @enderror"
               id="update_password_password" name="password" autocomplete="new-password">
        @error('updatePassword.password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="col-md-6">
        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" class="form-control @error('updatePassword.password_confirmation') is-invalid @enderror"
               id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password">
        @error('updatePassword.password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Save Button -->
    <div class="col-12 d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>

    @if (session('status') === 'password-updated')
        <div class="col-12">
            <div class="alert alert-success mt-3" role="alert">
                Password updated successfully.
            </div>
        </div>
    @endif
</form>


</section>
