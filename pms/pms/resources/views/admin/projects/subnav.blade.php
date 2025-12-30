@php
    $tabs = [
        'overview' => ['label' => 'Overview', 'route' => 'admin.projects.show'],
        'members' => ['label' => 'Members', 'route' => 'admin.project-members.index'],
        'files' => ['label' => 'Files', 'route' => '#'],
        'milestones' => ['label' => 'Milestones', 'route' => '#'],
        'tasks' => ['label' => 'Tasks', 'route' => '#'],
        'board' => ['label' => 'Task Board', 'route' => '#'],
        'timesheet' => ['label' => 'Timesheet', 'route' => '#'],
        'discussion' => ['label' => 'Discussion', 'route' => '#'],
        'notes' => ['label' => 'Notes', 'route' => '#'],
    ];
@endphp

<ul class="nav nav-tabs mb-3">
    @foreach($tabs as $key => $tab)
        <li class="nav-item">
            <a 
                class="nav-link {{ $activeTab == $key ? 'active' : '' }}"
                href="{{ $tab['route'] == '#' ? '#' : (isset($project) ? route($tab['route'], $project->id) : '#') }}"
            >
                {{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
