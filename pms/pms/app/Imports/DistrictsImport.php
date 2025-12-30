<?php
namespace App\Imports;

use App\Models\District;
use App\Models\State;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistrictsImport implements ToModel, WithHeadingRow
{
   public function model(array $row)
{
    //dd($row);
    // Trim and check if state_name is empty
    if (empty(trim($row['state_name'])) || empty(trim($row['district_name']))) {
        return null;
    }

    // Debugging: Check if state exists
    $state = State::where('name', trim($row['state_name']))->first();
    
    if (!$state) {
        \Log::error("State not found: " . $row['state_name']);
        return null;
    }

    return new District([
        'name' => trim($row['district_name']),
        'state_id' => $state->id,
    ]);
}

}

