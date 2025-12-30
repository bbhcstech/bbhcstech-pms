<!DOCTYPE html>
<html lang="en"> 

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Doctor Listing Software</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="admin/assets/img/favicon.png" rel="icon">
  <link href="admin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="admin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="admin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="admin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="admin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="admin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="admin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="admin/assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
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
                  <img width="50" height="300" src="admin/assets/img/doctor-logo.png" alt="">
                  <span class="d-none d-lg-block">Doctor Listing Admin</span>
                </a>
              </div><!-- End Logo -->

                   <div class="card mb-3">

                            <div class="card-body">
            
                               <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                  {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                                </div>
                              <x-auth-session-status class="mb-4" :status="session('status')" />
            
                                <form method="POST" action="{{ route('password.email') }}">
                                    @csrf
                            
                                    <!-- Email Address -->
                                    <div>
                                        <x-input-label for="email" :value="__('Email')" />
                                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>
                            
                                    <div class="flex items-center justify-end mt-4">
                                        <x-primary-button style="color: black !important;">
                                            {{ __('Email Password Reset Link') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                           </div>
                      </div>
        
                      <div class="credits">
                       
        
                    </div>
                  </div>
                </div>
        
              </section>
        
            </div>
          </main><!-- End #main -->
        
          <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
        
          <!-- Vendor JS Files -->
          <script src="admin/assets/vendor/apexcharts/apexcharts.min.js"></script>
          <script src="admin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
          <script src="admin/assets/vendor/chart.js/chart.umd.js"></script>
          <script src="admin/assets/vendor/echarts/echarts.min.js"></script>
          <script src="admin/assets/vendor/quill/quill.js"></script>
          <script src="admin/assets/vendor/simple-datatables/simple-datatables.js"></script>
          <script src="admin/assets/vendor/tinymce/tinymce.min.js"></script>
          <script src="admin/assets/vendor/php-email-form/validate.js"></script>
        
          <!-- Template Main JS File -->
          <script src="admin/assets/js/main.js"></script>
        
        </body>
        
        </html>
