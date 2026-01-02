<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\BusinessAddress;
use Illuminate\Http\Request;

class BusinessAddressController extends Controller
{
    public function index()
    {
        $addresses = BusinessAddress::all();
        return view('admin.settings.business-address.index', compact('addresses'));
    }

    public function create()
    {
        return view('admin.settings.business-address.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'address' => 'required|string',
            'country' => 'required|string|max:100',
            'tax_name' => 'nullable|string|max:100',
            'is_default' => 'sometimes|boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        if ($validated['is_default']) {
            BusinessAddress::where('is_default', true)->update(['is_default' => false]);
        }

        BusinessAddress::create($validated);

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Business address created successfully.');
    }

   public function edit(BusinessAddress $businessAddress)
{
    $addresses = BusinessAddress::all(); // sob business addresses niya aso
    return view('admin.settings.business-address.edit', compact('businessAddress', 'addresses'));
}

    public function update(Request $request, BusinessAddress $businessAddress)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'address' => 'required|string',
            'country' => 'required|string|max:100',
            'tax_name' => 'nullable|string|max:100',
            'is_default' => 'sometimes|boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        if ($validated['is_default'] && !$businessAddress->is_default) {
            BusinessAddress::where('is_default', true)
                ->where('id', '!=', $businessAddress->id)
                ->update(['is_default' => false]);
        }

        $businessAddress->update($validated);

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Business address updated successfully.');
    }

    public function destroy(BusinessAddress $businessAddress)
    {
        if (BusinessAddress::count() <= 1) {
            return redirect()->route('admin.settings.business-address.index')
                ->with('error', 'Cannot delete the only business address.');
        }

        if ($businessAddress->is_default) {
            $newDefault = BusinessAddress::where('id', '!=', $businessAddress->id)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $businessAddress->delete();

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Business address deleted successfully.');
    }

    public function makeDefault(Request $request, BusinessAddress $businessAddress)
    {
        BusinessAddress::where('is_default', true)->update(['is_default' => false]);

        $businessAddress->update(['is_default' => true]);

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Default business address updated.');
    }
}
