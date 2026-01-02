@extends('admin.layout.app')

@section('content')
<div class="container-xxl">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New Business Address</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.business-address.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location"
                                   value="{{ old('location') }}"
                                   placeholder="e.g., Kolkata, Delhi Office" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Full Address *</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3"
                                      placeholder="Complete address including street, city, state, zip code"
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country *</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country"
                                   value="{{ old('country') }}"
                                   placeholder="e.g., India" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tax_name" class="form-label">Tax Name (Optional)</label>
                            <input type="text" class="form-control @error('tax_name') is-invalid @enderror"
                                   id="tax_name" name="tax_name"
                                   value="{{ old('tax_name') }}"
                                   placeholder="e.g., GST, VAT, PAN">
                            @error('tax_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="is_default" name="is_default" value="1">
                                <label class="form-check-label" for="is_default">
                                    Set as default business address
                                </label>
                            </div>
                            <small class="text-muted">
                                If checked, this address will be used as primary for all business operations.
                            </small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings.business-address.index') }}"
                               class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
