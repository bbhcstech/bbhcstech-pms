<?php 
namespace App\Imports;

use App\Models\DoctorList;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class DoctorsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
{
    try {
        Log::info("Processing row: " . json_encode($row));

        // Find country
        $country = Country::where('name', trim($row['country_name']))->first();
        if (!$country) {
            Log::warning("Country not found: " . $row['country_name']);
            return null;
        }

        // Find state
        $state = State::where('name', trim($row['state_name']))->where('country_id', $country->id)->first();
        if (!$state) {
            Log::warning("State not found: " . $row['state_name']);
            return null;
        }

        // Find district
        $district = District::where('name', trim($row['district_name']))->where('state_id', $state->id)->first();
        if (!$district) {
            Log::warning("District not found: " . $row['district_name']);
            return null;
        }

        // Find category
        $category = Category::where('name', trim($row['category']))->first();
        if (!$category) {
            Log::warning("Category not found: " . $row['category']);
            return null;
        }

        Log::info("Inserting Doctor: " . $row['name']);

        return new DoctorList([
            'name'               => trim($row['name']),
            'phone_number' => trim($row['mobile_number_1'] ?? ''),
            'personal_phone_number' => trim($row['mobile_number_2'] ?? ''),
            'category_id'        => $category->id,
            'address'            => trim($row['address'] ?? ''),
            'email' => trim($row['email'] ?? ''),
            'degree' => trim($row['degree'] ?? ''),
            'country_id'         => $country->id,
            'country_name'       => $country->name,
            'state_id'           => $state->id,
            'state_name'         => $state->name,
            'district_id'        => $district->id,
            'district_name'      => $district->name,
            'website'            => trim($row['website']),
            'facebook'           => trim($row['facebook']),
            'instagram'          => trim($row['instagram']),
            'latitude'           => trim($row['latitude']),
            'logitude'          => trim($row['longitude']),
            
            'fees'               => trim($row['fees']),
            'whatsapp'           => trim($row['whatsapp']),
            'language'           => trim($row['language']),
            'experience'         => trim($row['experience']),                                                                                                                
            'mode_of_payment'    => trim($row['mode_of_payment']),
            'loc1'               => trim($row['location_1']),
            'loc2'              => trim($row['location_2']),
            'loc3'              => trim($row['location_3']),
            'loc4'              => trim($row['location_4']),
            'loc5'              => trim($row['location_5']),
            'membership'        => trim($row['membership']),
        ]);
    } catch (\Exception $e) {
        Log::error("Error processing row: " . json_encode($row) . " - " . $e->getMessage());
        return null; 
    }
}

}
