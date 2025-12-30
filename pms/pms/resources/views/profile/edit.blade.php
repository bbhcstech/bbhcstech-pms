@extends('admin.layout.app')

@section('title', 'Home Page')

@section('content')

  <main id="main" class="main">
    <div class="container mt-4">

      <div class="pagetitle">
        <h1>Profile</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
            @if(Auth::check())
          @if(Auth::user()->role === 'admin')

          <a href="{{route('dashboard')}}" class="logo d-flex align-items-center">
            <img src="admin/assets/img/logo.png" alt="">
            <span class="d-none d-lg-block">Micro Poem Admin</span>
          </a>
          @elseif(Auth::user()->role === 'manager')

          <a href="{{route('manager.dashboard')}}" class="logo d-flex align-items-center">
            <img src="admin/assets/img/logo.png" alt="">
            <span class="d-none d-lg-block">Micro Poem Manager</span>
          </a>

          @endif
          @endif
            </li>
            <li class="breadcrumb-item">Users</li>
            <li class="breadcrumb-item active">Profile</li>
          </ol>
        </nav>
      </div><!-- End Page Title -->

      <section class="section profile">
        <div class="row">
        

          <div class="col-xl-8">

            <div class="card">
              <div class="card-body pt-3">
                <!-- Bordered Tabs -->
                <ul class="nav nav-tabs nav-tabs-bordered">

              

                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Edit Profile</button>
                  </li>

                  <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-settings">Settings</button>
                  </li>

                

                </ul>
                <div class="tab-content pt-2">

              
                <div class="tab-pane fade show active profile-overview" id="profile-overview">

                    <!-- Profile Edit Form -->
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                      <div class="max-w-xl">
                              @include('profile.partials.update-profile-information-form')
                          </div>
                      </div>
                    <!-- End Profile Edit Form -->

                  </div>

                  <div class="tab-pane fade pt-3" id="profile-settings">

                    <!-- Settings Form -->

                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                      <div class="max-w-xl">
                          @include('profile.partials.update-password-form')
                      </div>
                  </div>

                      <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                          <div class="max-w-xl">
                              @include('profile.partials.delete-user-form')
                          </div>
                      </div>`

                  <!-- End settings Form -->

                  </div>

                  

                </div><!-- End Bordered Tabs -->

              </div>
            </div>

          </div>
        </div>
      </section>
    </div>

  </main><!-- End #main -->

  @endsection