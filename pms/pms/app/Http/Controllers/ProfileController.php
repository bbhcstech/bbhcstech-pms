<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage; 
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        if (auth()->user()->role == 'admin' || auth()->user()->role == 'employee' || auth()->user()->role == 'client'  ) {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
        
        }
        
   
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();

    $user->fill($request->validated());

    // Handle profile image upload
    if ($request->hasFile('profile_image')) {
        $image = $request->file('profile_image');
        $imageName = time() . '-' . $image->getClientOriginalName();

        // Store in: public/admin/uploads/profile-images/
        $image->move(public_path('admin/uploads/profile-images'), $imageName);

        // Save the relative path to DB (you can change this path structure)
        $user->profile_image = 'admin/uploads/profile-images/' . $imageName;
    }

    // Reset email verification if email changed
    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
