<li class="list-group-item" data-id="{{ $designation->id }}">
    <span class="drag-handle" style="cursor: move;">&#x2630;</span>
    @if($designation->parent_id)
        &rarr; 
    @endif
    {{ $designation->name }}

    @if($designation->children->count())
        <ul class="list-group mt-1 ms-4">
            @foreach($designation->children as $child)
                @include('admin.designations.partials.designation-item', ['designation' => $child])
            @endforeach
        </ul>
    @endif
</li>
