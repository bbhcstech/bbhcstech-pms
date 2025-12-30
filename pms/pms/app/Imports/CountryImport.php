<?php

namespace App\Imports;

use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CountryImport implements ToModel, WithHeadingRow
{
   public function model(array $row)
{
    // Check if the correct column exists
    if (!isset($row['name']) || empty($row['name'])) {
        return null;
    }

    $countryName = trim($row['name']); // Use column name

    // Check if the country already exists
    if (!Country::where('name', $countryName)->exists()) {
        return new Country([
            'name' => $countryName,
        ]);
    }

    return null;
}

}

