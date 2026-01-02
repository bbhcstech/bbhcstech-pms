<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    // Show company settings page
    public function index()
    {
        $company = CompanySetting::first() ?? new CompanySetting();
        return view('admin.settings.company', compact('company'));
    }

    // Store or update company settings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'    => 'required|string|max:255',
            'company_email'   => 'required|email|max:255',
            'company_phone'   => 'required|string|max:25',
            'company_website' => 'nullable|url|max:255',
        ]);

        CompanySetting::updateOrCreate(
            ['id' => 1], // single-row system
            $validated
        );

        return back()->with('success', 'Company settings updated successfully');
    }

    public function destroy()
{
    CompanySetting::query()->delete(); // reset all settings

    return redirect()
        ->route('settings.company')
        ->with('success', 'Company settings reset successfully. Please add again.');
}

}
