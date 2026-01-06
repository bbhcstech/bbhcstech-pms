<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProfileSetting;

class ProfileSettingController extends Controller
{
    public function __construct()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Show Profile Settings Page
     */
    public function index()
    {
        $settings = ProfileSetting::where('visible', 1)
            ->orderBy('order')
            ->get();

        return view('admin.settings.profile.index', compact('settings'));
    }

    /**
     * Update Profile Settings
     */
    public function update(Request $request)
    {
        $settings = ProfileSetting::all();

        foreach ($settings as $setting) {

            $value = null;

            // Checkbox handling
            if ($setting->type === 'checkbox') {
                $value = $request->has($setting->key) ? 1 : 0;
            } else {
                $value = $request->input($setting->key);
            }

            // Required validation
            if ($setting->required && is_null($value)) {
                return back()->withErrors([
                    $setting->key => $setting->label . ' is required'
                ]);
            }

            $setting->update([
                'value' => $value
            ]);
        }

        return back()->with('success', 'Profile settings updated successfully!');
    }

    /**
     * Add New Dynamic Field (Admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'key'   => 'required|unique:profile_settings,key',
            'label' => 'required',
            'type'  => 'required|in:text,email,number,textarea,select,checkbox',
        ]);

        ProfileSetting::create([
            'key'      => $request->key,
            'label'    => $request->label,
            'type'     => $request->type,
            'options'  => $request->options,
            'required' => $request->required ?? 0,
            'visible'  => 1,
            'order'    => ProfileSetting::max('order') + 1,
        ]);

        return back()->with('success', 'New profile field added!');
    }
}
