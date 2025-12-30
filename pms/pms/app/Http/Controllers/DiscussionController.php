<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Project;
use App\Models\DiscussionCategory;
use App\Models\DiscussionFile;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;
use Auth;

class DiscussionController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
         $categories = DiscussionCategory::all(); // create model if not already
        $discussions = Discussion::where('project_id', $projectId)->latest()->get();

        return view('admin.discussions.index', compact('project', 'discussions', 'categories'));
    }

           public function create($projectId)
        {
            $project = Project::findOrFail($projectId);
            $categories = DiscussionCategory::all(); // create model if not already
            return view('admin.discussions.create', compact('project', 'categories'));
        }


public function store(Request $request, $projectId)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'reply' => 'required|string',
        'discussion_category_id' => 'required|exists:discussion_categories,id',
        'file' => 'nullable|file|max:2048',
    ]);

    // Create the discussion
    $discussion = new Discussion();
    $discussion->project_id = $projectId;
    $discussion->discussion_category_id = $request->discussion_category_id;
    $discussion->title = $request->title;
    $discussion->user_id = auth()->id();
    $discussion->added_by = auth()->id();
    $discussion->save();

    // Save the reply using DiscussionReply (not DiscussionMessage anymore)
    $reply = DiscussionReply::create([
        'company_id' => null, // Or use auth()->user()->company_id
        'discussion_id' => $discussion->id,
        'user_id' => auth()->id(),
        'body' => $request->reply,
    ]);

    // Handle file upload
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $hashname = time() . '-' . $originalName;
        $size = $file->getSize();

        $file->move(public_path('admin/uploads/discussion-files'), $hashname);

        DiscussionFile::create([
            'company_id' => null, // Or auth()->user()->company_id
            'user_id' => auth()->id(),
            'discussion_id' => $discussion->id,
            'discussion_reply_id' => $reply->id,
            'filename' => $originalName,
            'description' => null,
            'google_url' => null,
            'hashname' => $hashname,
            'size' => $size,
            'dropbox_link' => null,
            'external_link_name' => null,
        ]);
    }

    return redirect()->route('projects.discussions.index', $projectId)
                     ->with('success', 'Discussion created successfully.');
}



    public function show($projectId, $discussionId)
    {
        $project = Project::findOrFail($projectId);
        $discussion = Discussion::where('project_id', $projectId)->findOrFail($discussionId);
        return view('admin.discussions.show', compact('project', 'discussion'));
    }

    public function destroy($projectId, $discussionId)
    {
        $discussion = Discussion::where('project_id', $projectId)->findOrFail($discussionId);
        $discussion->delete();

        return redirect()->route('projects.discussions.index', $projectId)->with('success', 'Discussion deleted.');
    }
    
    public function disscatstore(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:20',
    ]);

    DiscussionCategory::create([
        'company_id' => auth()->user()->company_id ?? null,
        'order' => DiscussionCategory::max('order') + 1,
        'name' => $request->name,
        'color' => $request->color,
    ]);

    return redirect()->back()->with('success', 'Discussion category added successfully.');
}

public function repliesstore(Request $request, $projectId, $discussionId)
{
    $request->validate([
        'body' => 'required|string',
        'file' => 'nullable|file|max:2048',
    ]);

    // Create reply
    $reply = DiscussionReply::create([
        'company_id' => auth()->user()->company_id ?? null,
        'discussion_id' => $discussionId,
        'user_id' => auth()->id(),
        'body' => $request->body,
    ]);

    // Handle file upload (optional)
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $hashname = time() . '-' . $originalName;
        $size = $file->getSize();

        $file->move(public_path('admin/uploads/discussion-files'), $hashname);

        DiscussionFile::create([
            'company_id' => auth()->user()->company_id ?? null,
            'user_id' => auth()->id(),
            'discussion_id' => $discussionId,
            'discussion_reply_id' => $reply->id,
            'filename' => $originalName,
            'description' => null,
            'google_url' => null,
            'hashname' => $hashname,
            'size' => $size,
            'dropbox_link' => null,
            'external_link_name' => null,
        ]);
    }

    return redirect()->back()->with('success', 'Reply added successfully.');
}
      
        

}
