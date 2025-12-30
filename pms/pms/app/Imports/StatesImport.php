<?php

namespace App\Imports;

use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StatesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Find the country by name and get its ID
        $country = Country::where('name', $row['country_name'])->first();

        if (!$country) {
            return null; // Skip if country not found
        }

        return new State([
            'name'       => $row['state_name'],
            'country_id' => $country->id,
        ]);
    }
}
