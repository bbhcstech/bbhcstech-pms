<?php

namespace App\Imports;

use App\Models\LeadContact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class LeadsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new LeadContact([
            'contact_name' => $row['contact_name'] ?? $row['name'] ?? null,
            'email' => $row['email'] ?? null,
            'company_name' => $row['company_name'] ?? null,
            'phone' => $row['phone'] ?? null,
            'mobile' => $row['mobile'] ?? null,
            'website' => $row['website'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'country' => $row['country'] ?? null,
            'lead_source' => $row['lead_source'] ?? 'import',
            'status' => $row['status'] ?? 'new',
            'lead_score' => $row['lead_score'] ?? 0,
            'tags' => $row['tags'] ?? null,
            'lead_owner_id' => $row['lead_owner_id'] ?? Auth::id(),
            'added_by' => Auth::id(),
            'added_by_designation' => Auth::user()->designation ?? null,
            'lead_owner_designation' => Auth::user()->designation ?? null,
            'description' => $row['description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'contact_name' => 'required',
            'email' => 'required|email',
        ];
    }
}
