<!DOCTYPE html>
<html lang="en"> 

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Reset Password - Doctor A2Z</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{ asset('admin/assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('admin/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('admin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/assets/css/style.css') }}" rel="stylesheet">
</head>

<body>

<main>
  <div class="container">
    <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

            <div class="d-flex justify-content-center py-4">
              <a href="{{ route('login') }}" class="logo d-flex align-items-center w-auto">
                <img src="{{ asset('admin/assets/img/doctor-logo.png') }}" alt="" width="50">
                <span class="d-none d-lg-block">Doctor A2Z Admin</span>
              </a>
            </div>

            <div class="card mb-3">
              <div class="card-body">
                <div class="pt-4 pb-2">
                  <h5 class="card-title text-center pb-0 fs-4">Reset Your Password</h5>
                  <p class="text-center small">Enter your new password below</p>
                </div>

                <!-- Show Validation Errors -->
                @if ($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}">
                  @csrf

                  <!-- Hidden Token -->
                  <input type="hidden" name="token" value="{{ $token }}">

                  <!-- Email -->
                  <div class="col-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" required autofocus>
                  </div>

                  <!-- Password -->
                  <div class="col-12 mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                  </div>

                  <!-- Confirm Password -->
                  <div class="col-12 mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                  </div>

                  <!-- Submit -->
                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Reset Password</button>
                  </div>
                </form>

              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>
</main>

<!-- Scripts -->
<script src="{{ asset('admin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/main.js') }}"></script>

</body>
</html>


