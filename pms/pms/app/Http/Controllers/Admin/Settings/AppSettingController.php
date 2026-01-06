<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    /**
     * 1. App Settings Page (Main Page)
     */
    public function appSettings(Request $request)
    {
        $page = 'app';

        // Get all settings grouped by section
        $settings = AppSetting::where('page', $page)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        // Get all sections for dropdown
        $sections = AppSetting::where('page', $page)
            ->distinct('section')
            ->pluck('section')
            ->filter();

        // Get available pages for navigation
        $availablePages = ['app', 'client-signup', 'file-upload', 'google-map'];

        // Get page labels for display
        $pageLabels = [
            'app' => 'App Settings',
            'client-signup' => 'Client Sign up Settings',
            'file-upload' => 'File Upload Settings',
            'google-map' => 'Google Map Settings'
        ];

        $pageTitle = 'App Settings';

        return view('admin.settings.app.app', compact('settings', 'page', 'sections', 'availablePages', 'pageLabels', 'pageTitle'));
    }

    /**
     * 2. Client Signup Settings Page
     */
    public function clientSignupSettings(Request $request)
    {
        $page = 'client-signup';

        // Get all settings grouped by section
        $settings = AppSetting::where('page', $page)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        // Get all sections for dropdown
        $sections = AppSetting::where('page', $page)
            ->distinct('section')
            ->pluck('section')
            ->filter();

        // Get available pages for navigation
        $availablePages = ['app', 'client-signup', 'file-upload', 'google-map'];

        // Get page labels for display
        $pageLabels = [
            'app' => 'App Settings',
            'client-signup' => 'Client Sign up Settings',
            'file-upload' => 'File Upload Settings',
            'google-map' => 'Google Map Settings'
        ];

        $pageTitle = 'Client Sign up Settings';

        return view('admin.settings.app.app', compact('settings', 'page', 'sections', 'availablePages', 'pageLabels', 'pageTitle'));
    }

    /**
     * 3. File Upload Settings Page
     */
    public function fileUploadSettings(Request $request)
    {
        $page = 'file-upload';

        // Get all settings grouped by section
        $settings = AppSetting::where('page', $page)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        // Get all sections for dropdown
        $sections = AppSetting::where('page', $page)
            ->distinct('section')
            ->pluck('section')
            ->filter();

        // Get available pages for navigation
        $availablePages = ['app', 'client-signup', 'file-upload', 'google-map'];

        // Get page labels for display
        $pageLabels = [
            'app' => 'App Settings',
            'client-signup' => 'Client Sign up Settings',
            'file-upload' => 'File Upload Settings',
            'google-map' => 'Google Map Settings'
        ];

        $pageTitle = 'File Upload Settings';

        return view('admin.settings.app.app', compact('settings', 'page', 'sections', 'availablePages', 'pageLabels', 'pageTitle'));
    }

    /**
     * 4. Google Map Settings Page
     */
    public function googleMapSettings(Request $request)
    {
        $page = 'google-map';

        // Get all settings grouped by section
        $settings = AppSetting::where('page', $page)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        // Get all sections for dropdown
        $sections = AppSetting::where('page', $page)
            ->distinct('section')
            ->pluck('section')
            ->filter();

        // Get available pages for navigation
        $availablePages = ['app', 'client-signup', 'file-upload', 'google-map'];

        // Get page labels for display
        $pageLabels = [
            'app' => 'App Settings',
            'client-signup' => 'Client Sign up Settings',
            'file-upload' => 'File Upload Settings',
            'google-map' => 'Google Map Settings'
        ];

        $pageTitle = 'Google Map Settings';

        return view('admin.settings.app.app', compact('settings', 'page', 'sections', 'availablePages', 'pageLabels', 'pageTitle'));
    }

    /**
     * Update Settings
     */
    public function update(Request $request)
    {
        if (!$request->has('settings')) {
            return back()->with('success', 'No changes found.');
        }

        foreach ($request->settings as $id => $value) {
            $setting = AppSetting::find($id);

            if (!$setting) {
                continue;
            }

            // Checkbox handling
            if ($setting->type === 'checkbox') {
                $value = isset($value) ? 1 : 0;
            }

            // Array (select multiple)
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $setting->update([
                'value' => $value
            ]);
        }

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Add New Dynamic Field
     */
    public function addField(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:app_settings,key',
            'label' => 'required|string',
            'type' => 'required|in:text,select,checkbox,textarea,number,email',
            'page' => 'required|string',
            'section' => 'required|string',
            'options' => 'nullable|string',
            'placeholder' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Parse options if provided
        $options = null;
        if ($request->type === 'select' && $request->options) {
            $optionsArray = array_map('trim', explode(',', $request->options));
            $formattedOptions = [];
            foreach ($optionsArray as $option) {
                $formattedOptions[] = [
                    'value' => strtolower(str_replace(' ', '_', $option)),
                    'label' => $option
                ];
            }
            $options = json_encode($formattedOptions);
        }

        AppSetting::create([
            'key' => $request->key,
            'label' => $request->label,
            'type' => $request->type,
            'page' => $request->page,
            'section' => $request->section,
            'options' => $options,
            'placeholder' => $request->placeholder,
            'value' => $request->type === 'checkbox' ? 0 : ($request->default_value ?? ''),
            'sort_order' => $request->sort_order ?? 999,
        ]);

        return back()->with('success', 'New field added successfully!');
    }
}
