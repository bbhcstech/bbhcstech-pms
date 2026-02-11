<?php

namespace App\Http\Controllers;

use App\Models\Designation; // Make sure this line is at the top
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Get active designations from database
        $designations = Designation::where('status', 'active')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        if (auth()->user()->role == 'admin' || auth()->user()->role == 'employee' || auth()->user()->role == 'client') {
            return view('profile.edit', [
                'user' => $request->user(),
                'designations' => $designations // Pass designations to view
            ]);
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Validate designation exists in database
        if ($request->has('designation')) {
            $designationExists = Designation::where('name', $request->designation)
                ->where('status', 'active')
                ->exists();

            if (!$designationExists && $request->designation !== '' && $request->designation !== null) {
                return back()->withErrors(['designation' => 'Selected designation is not valid.']);
            }
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '-' . $image->getClientOriginalName();

            // Store in: public/admin/uploads/profile-images/
            $image->move(public_path('admin/uploads/profile-images'), $imageName);

            // Save the relative path to DB
            $user->profile_image = 'admin/uploads/profile-images/' . $imageName;
        }

        // Update user data (excluding password fields)
        $user->name = $request->name;
        $user->email = $request->email;
        $user->designation = $request->designation;
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->marital_status = $request->marital_status;
        $user->address = $request->address;
        $user->about = $request->about;
        $user->country = $request->country;
        $user->language = $request->language;
        $user->slack_id = $request->slack_id;
        $user->email_notify = $request->email_notify;
        $user->google_calendar = $request->google_calendar;

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
