@extends('admin.layout.app')
@section('title', 'Edit Holiday')

@section('content')
<main class="main">
    <div class="container py-4">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('holidays.update', $holiday->id) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded p-3">
                <h4 class="mb-3 font-weight-normal border-bottom pb-2">Edit Holiday</h4>

                <!--{{-- Holiday Items --}}-->
                <!--<div id="holidayItems">-->
                <!--    <div class="row holiday-item mb-3">-->
                <!--        <div class="col-lg-6 mb-3">-->
                <!--            <label>Date <span class="text-danger">*</span></label>-->
                <!--            <input type="date" name="date[]" class="form-control"-->
                <!--                   value="{{ old('date.0', $holiday->date) }}" required>-->
                <!--        </div>-->
                <!--        <div class="col-lg-6 mb-3 d-flex align-items-end">-->
                <!--            <div class="w-100">-->
                <!--                <label>Occasion <span class="text-danger">*</span></label>-->
                <!--                <input type="text" name="occassion[]" class="form-control"-->
                <!--                       value="{{ old('occassion.0', $holiday->title) }}" required>-->
                <!--            </div>-->
                <!--            {{-- Only show remove button for dynamically added items --}}-->
                <!--            <button type="button" class="btn btn-danger btn-sm ms-2 remove-item d-none">✕</button>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                {{-- Holiday Items --}}
                <div id="holidayItems">
                    @foreach ($holiday->group->holidays as $h)
                        <div class="row holiday-item mb-3">
                            <input type="hidden" name="holiday_id[]" value="{{ $h->id }}">
                            
                            <div class="col-lg-6 mb-3">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="date[]" class="form-control"
                                       value="{{ old('date.' . $loop->index, $h->date) }}" required>
                            </div>
                            
                            <div class="col-lg-6 mb-3 d-flex align-items-end">
                                <div class="w-100">
                                    <label>Occasion <span class="text-danger">*</span></label>
                                    <input type="text" name="occassion[]" class="form-control"
                                           value="{{ old('occassion.' . $loop->index, $h->title) }}" required>
                                </div>
                                
                                {{-- Show remove button for all except the first --}}
                                <button type="button" class="btn btn-danger btn-sm ms-2 remove-item {{ $loop->first ? 'd-none' : '' }}">✕</button>
                            </div>
                        </div>
                    @endforeach
                </div>



                {{-- Department / Designation / Employment Type --}}
                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <label>Department</label>
                        <select class="form-control multiple-users" multiple name="department_id_json[]"
                                id="selectdepartment" data-live-search="true" data-size="8">
                            @foreach ($department as $team)
                                <option value="{{ $team->id }}"
                                    {{ in_array($team->id, old('department_id_json', json_decode($holiday->department_id_json, true) ?? [])) ? 'selected' : '' }}>
                                    {{ $team->dpt_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label>Designation</label>
                        <select class="form-control multiple-users" multiple name="designation_id_json[]"
                                id="selectdesignation" data-live-search="true" data-size="8">
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}"
                                    {{ in_array($designation->id, old('designation_id_json', json_decode($holiday->designation_id_json, true) ?? [])) ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label>Employment Type</label>
                        <select class="form-control select2" name="employment_type_json[]" multiple>
                            @php
                                $selectedEmployment = old('employment_type_json', json_decode($holiday->employment_type_json, true) ?? []);
                            @endphp
                            <option value="full_time" {{ in_array('full_time', $selectedEmployment) ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ in_array('part_time', $selectedEmployment) ? 'selected' : '' }}>Part Time</option>
                            <option value="on_contract" {{ in_array('on_contract', $selectedEmployment) ? 'selected' : '' }}>On Contract</option>
                            <option value="internship" {{ in_array('internship', $selectedEmployment) ? 'selected' : '' }}>Internship</option>
                            <option value="trainee" {{ in_array('trainee', $selectedEmployment) ? 'selected' : '' }}>Trainee</option>
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Remove dynamically added items
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.holiday-item').remove();
        }
    });

    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
        $('#selectdesignation, #selectdepartment').selectpicker({
            actionsBox: true,
            selectAllText: "selectAll",
            deselectAllText: "deselectAll",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " Selected";
            }
        });
    });
</script>
@endpush
