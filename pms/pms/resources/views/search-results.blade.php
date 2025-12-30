@extends('admin.layout.app')

@section('title', 'Search Page')  

@section('content')
    <h4>Search Results for "{{ $query }}" in {{ ucfirst($type) }}</h4>

    @if($results->isEmpty())
        <p>No results found.</p>
    @else
        <ul>
            @foreach($results as $result)
                <li>
                    @php
                        $link = '#';
                        switch ($type) {
                            case 'ticket':
                                $link = route('tickets.index') . '?search=' . $query;
                                break;
                            case 'task':
                                $link = route('tasks.index') . '?search=' . $query;
                                break;
                            case 'project':
                                $link = route('projects.index') . '?search=' . $query;
                                break;
                            case 'employee':
                                $link = route('employees.index') . '?search=' . $query;
                                break;
                            case 'client':
                                $link = route('clients.index') . '?search=' . $query;
                                break;
                        }
                    @endphp
                    <a href="{{ $link }}">
                        {{ $result->name ?? $result->title ?? $result->subject ?? 'N/A' }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
