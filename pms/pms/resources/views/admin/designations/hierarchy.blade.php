@extends('admin.layout.app')

@section('title', 'Designation Hierarchy')

@section('content')
<main class="main py-4">
    <div class="container">
        <h4 class="mb-4">Designation Hierarchy</h4>

        &nbsp;
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div>
                <a href="{{ route('designations.create') }}" class="btn btn-primary mb-0">Add Designation</a>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="btn-group" role="group" aria-label="View Options">
                    <a href="{{ route('designations.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip" title="Table View">
                        <i class="side-icon bi bi-list-ul"></i>
                    </a>
                    <a href="{{ route('designations.hierarchy') }}" class="btn btn-secondary f-14" data-toggle="tooltip" title="Hierarchy">
                        <i class="bi bi-diagram-3"></i>
                    </a>
                </div>
            </div>
        </div>
        &nbsp;

        <!-- Split row: Left = hierarchy, Right = chart -->
        <div class="row mb-4">
            <!-- Left column: drag & drop hierarchy -->
            <div class="col-md-6">
                <p>Drag & drop to restructure the designations</p>

                <ul id="hierarchyList" class="list-group">
                    @foreach($designations->whereNull('parent_id') as $designation)
                        @include('admin.designations.partials.designation-item', ['designation' => $designation])
                    @endforeach
                </ul>
                
              

                <button id="saveHierarchy" class="btn btn-primary mt-3">New Hierarchy</button>
            </div>

            <!-- Right column: organizational chart -->
            <div class="col-md-6">
                <div id="chartDiv" class="pt-3 bg-white rounded-bottom" style="height: 800px;"></div>
            </div>
        </div>
    </div>
</main>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://code.jscharting.com/latest/jscharting.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Make hierarchy sortable
    new Sortable(document.getElementById('hierarchyList'), {
        group: 'nested',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        handle: '.drag-handle'
    });

    // Save hierarchy
    document.getElementById('saveHierarchy').addEventListener('click', function() {
        let hierarchy = [];
        function traverse(list, parentId = null) {
            list.querySelectorAll('li').forEach((li, index) => {
                const id = li.dataset.id;
                hierarchy.push({ id: id, parent_id: parentId, order: index });
                const children = li.querySelector('ul');
                if(children) traverse(children, id);
            });
        }
        traverse(document.getElementById('hierarchyList'));

        fetch('{{ route("designations.save-hierarchy") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ hierarchy })
        }).then(res => res.json())
          .then(data => alert(data.message))
          .catch(err => alert('Something went wrong'));
    });

    // JSCharting chart
  // JSCharting chart
const chart = JSC.chart('chartDiv', {
    debug: false, // ✅ disable the yellow info icon
    type: 'organization',
    series: [{
        points: [
            { id: 'ceo', parent: null, label_text: 'CEO' },
            { id: 'cto', parent: 'ceo', label_text: 'CTO' },
            { id: 'cfo', parent: 'ceo', label_text: 'CFO' },
            { id: 'dev', parent: 'cto', label_text: 'Senior Developer' },
            { id: 'designer', parent: 'cto', label_text: 'Graphics Designer' },
            { id: 'sales', parent: 'cfo', label_text: 'Sales Executive' }
        ]
    }],
    title_label_text: 'Company Structure'
});

});
</script>
@endpush
@endsection