@extends('admin.layout.app')
@section('title', 'Add Holiday')

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

        {{-- Form Start --}}
        <form method="POST" action="{{ route('holidays.store') }}" id="save-holiday-data-form">
            @csrf
            <div class="bg-white rounded p-3">
                <h4 class="mb-3 font-weight-normal border-bottom pb-2">Add Holiday</h4>

                {{-- Holiday Items --}}
                <div id="holidayItems">
                    <div class="row holiday-item mb-3">
                        <div class="col-lg-6 mb-3">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="date[]" class="form-control" required>
                        </div>
                        <div class="col-lg-6 mb-3 d-flex align-items-end">
                            <div class="w-100">
                                <label>Occasion <span class="text-danger">*</span></label>
                                <input type="text" name="occassion[]" class="form-control" placeholder="Occasion" required>
                            </div>
                            {{-- Remove Button (hidden for first row) --}}
                            <button type="button" class="btn btn-danger btn-sm ms-2 remove-item d-none">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Add More --}}
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <a href="javascript:;" id="add-holiday-item" class="btn btn-link">
                            <i class="bi bi-plus-circle"></i> Add More
                        </a>
                    </div>
                </div>

                {{-- Department / Designation / Employment Type --}}
                <div class="row mb-3">
                        <div class="col-lg-6 mb-3">
                            <label>Department</label>
                          
                             <select class="form-control multiple-users" multiple name="department_id_json[]"
                                    id="selectdepartment" data-live-search="true" data-size="8">
                                @foreach ($department as $team)
                                    <option value="{{ $team->id }}">{{ $team->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-6 mb-3">
                            <label>Designation</label>
                         
                                 <select class="form-control multiple-users" multiple name="designation_id_json[]"
                                    id="selectdesignation" data-live-search="true" data-size="8">
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>


                    <div class="col-lg-6 mb-3">
                        <label>Employment Type</label>
                        <select class="form-control select2" name="employment_type_json[]" multiple>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="on_contract">On Contract</option>
                            <option value="internship">Internship</option>
                            <option value="trainee">Trainee</option>
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mb-3">
                    <button type="submit" class="btn btn-success">Save</button>
                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
        {{-- Form End --}}
    </div>
</main>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('add-holiday-item');
    const holidayItems = document.getElementById('holidayItems');

    addBtn.addEventListener('click', function () {
        const item = document.createElement('div');
        item.classList.add('row', 'holiday-item', 'mb-3');

        item.innerHTML = `
            <div class="col-lg-6 mb-3">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" name="date[]" class="form-control" required>
            </div>
            <div class="col-lg-6 mb-3 d-flex align-items-end">
                <div class="w-100">
                    <label>Occasion <span class="text-danger">*</span></label>
                    <input type="text" name="occassion[]" class="form-control" placeholder="Occasion" required>
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2 remove-item">✕</button>
            </div>
        `;

        holidayItems.appendChild(item);
    });

    // Remove item
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.holiday-item').remove();
        }
    });
});
</script>

   
    <script>
        $(document).ready(function() {
            $('#department, #designation').multiselect({
                includeSelectAllOption: true,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                maxHeight: 300,
                nonSelectedText: 'Nothing selected',
                selectAllText: 'Select All',
                allSelectedText: 'All selected',
                numberDisplayed: 2
            });
        });
    </script>
<script>


    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
    });
</script>


<script>

    $(document).ready(function() {
        $("#selectdesignation").selectpicker({
            actionsBox: true,
            selectAllText: "selectAll",
            deselectAllText: "deselectAll",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + "Selected";
            }
        });
        
         $("#selectdepartment").selectpicker({
            actionsBox: true,
            selectAllText: "selectAll",
            deselectAllText: "deselectAll",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + "Selected";
            }
        });
    });
    </script>
@endpush
