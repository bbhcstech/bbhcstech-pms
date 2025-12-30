<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserActivity;
use App\Models\ProjectActivity;
use App\Models\TicketActivity;
use App\Models\Project;

class AdminActivityController extends Controller
{
    public function projectActivity($projectId)
    {
        $project = Project::findOrFail($projectId);
    
        // Filter project-specific activities
        $projectActivities = ProjectActivity::where('project_id', $projectId)->latest()->get();
    
        // User activities optionally filtered (if you log project info in user_activities, else skip)
        $userActivities = UserActivity::where('activity', 'like', "%project: {$project->name}%")->latest()->get();
    
        // Ticket activities filtered if your ticket_activities have a relation to this project
        $ticketActivities = TicketActivity::where('project_id', $projectId)->latest()->get();
    
        return view('admin.activities.index', compact('project', 'projectActivities', 'userActivities', 'ticketActivities'));
    }
}
