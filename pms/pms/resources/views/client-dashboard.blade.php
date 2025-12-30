@extends('admin.layout.app')

@section('title', 'Client Dashboard')

@section('content')
<main id="main" class="main">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

      {{-- Welcome Card --}}
      <div class="row g-4 mb-4">
        <div class="col-xxl-8">
          <div class="card shadow-sm rounded-4">
            <div class="row align-items-center">
              <div class="col-sm-7">
                <div class="card-body">
                  <h4 class="card-title text-primary mb-2 fw-semibold">
                    Welcome to PMS Client Panel 👋
                  </h4>
                  <p class="text-muted mb-3">
                    Manage employee attendance, tasks, and performance all in one place.
                  </p>
                  <a href="javascript:;" class="btn btn-outline-primary btn-sm rounded-pill">
                    View Badges
                  </a>
                </div>
              </div>
              <div class="col-sm-5 text-center">
                <img
                  src="{{ asset('admin/assets/img/illustrations/man-with-laptop.png') }}"
                  class="img-fluid"
                  style="max-height:180px"
                  alt="Dashboard Illustration">
              </div>
            </div>
          </div>
        </div>

        {{-- Stat Cards --}}
        <div class="col-xxl-4">
          <div class="row g-4">
            <div class="col-6">
              <div class="card h-100 shadow-sm rounded-4">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <img src="{{ asset('admin/assets/img/icons/unicons/chart-success.png') }}" height="40">
                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                  </div>
                  <small class="text-muted">Total</small>
                  <h4 class="fw-bold mb-1">0</h4>
                  <small class="text-success fw-medium">
                    <i class="bx bx-up-arrow-alt"></i> 0
                  </small>
                </div>
              </div>
            </div>

            <div class="col-6">
              <div class="card h-100 shadow-sm rounded-4">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <img src="{{ asset('admin/assets/img/icons/unicons/wallet-info.png') }}" height="40">
                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                  </div>
                  <small class="text-muted">Today</small>
                  <h4 class="fw-bold mb-1">0</h4>
                  <small class="text-success fw-medium">
                    <i class="bx bx-up-arrow-alt"></i> 0
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Charts Row --}}
      <div class="row g-4">
        <div class="col-xxl-8">
          <div class="card shadow-sm rounded-4">
            <div class="card-header bg-transparent d-flex justify-content-between">
              <h5 class="mb-0 fw-semibold">Total Revenue</h5>
              <i class="bx bx-dots-vertical-rounded text-muted"></i>
            </div>
            <div class="card-body">
              <div id="totalRevenueChart"></div>
            </div>
          </div>
        </div>

        <div class="col-xxl-4">
          <div class="card shadow-sm rounded-4 h-100">
            <div class="card-body text-center">
              <h6 class="fw-semibold mb-2">Company Growth</h6>
              <div id="growthChart"></div>
              <p class="mt-3 fw-medium text-success">62% Growth</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Order / Expense / Transactions --}}
      <div class="row g-4 mt-1">
        <div class="col-lg-4">
          <div class="card shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent">
              <h5 class="fw-semibold mb-0">Order Statistics</h5>
              <small class="text-muted">42.82k Total Sales</small>
            </div>
            <div class="card-body">
              <div id="orderStatisticsChart"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent">
              <h5 class="fw-semibold mb-0">Expense Overview</h5>
            </div>
            <div class="card-body">
              <div id="incomeChart"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent">
              <h5 class="fw-semibold mb-0">Transactions</h5>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0">
                <li class="d-flex justify-content-between mb-3">
                  <span>Paypal</span>
                  <strong class="text-success">+82.6 USD</strong>
                </li>
                <li class="d-flex justify-content-between">
                  <span>Credit Card</span>
                  <strong class="text-danger">-92.45 USD</strong>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>
@endsection
