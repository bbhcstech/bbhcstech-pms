<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContractType;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Normal Contract',
                'slug' => 'normal',
                'description' => 'Standard contract for regular services',
                'is_active' => true,
            ],
            [
                'name' => 'Special Contract',
                'slug' => 'special',
                'description' => 'Special terms and conditions contract',
                'is_active' => true,
            ],
            [
                'name' => 'Fixed Price Contract',
                'slug' => 'fixed-price',
                'description' => 'Contract with fixed price for the entire project',
                'is_active' => true,
            ],
            [
                'name' => 'Time & Material Contract',
                'slug' => 'time-material',
                'description' => 'Contract billed based on time and materials used',
                'is_active' => true,
            ],
            [
                'name' => 'Service Level Agreement',
                'slug' => 'sla',
                'description' => 'Contract with specific service level agreements',
                'is_active' => true,
            ],
            [
                'name' => 'Maintenance Contract',
                'slug' => 'maintenance',
                'description' => 'Contract for maintenance and support services',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            ContractType::create($type);
        }
    }
}
