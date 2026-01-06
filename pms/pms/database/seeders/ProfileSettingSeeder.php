<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfileSetting;

class ProfileSettingSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            [
                'key' => 'app_name',
                'label' => 'Application Name',
                'type' => 'text',
                'required' => true,
                'visible' => true,
                'order' => 1,
            ],
            [
                'key' => 'timezone',
                'label' => 'Timezone',
                'type' => 'select',
                'options' => ['UTC', 'Asia/Kolkata', 'Asia/Dhaka'],
                'required' => true,
                'visible' => true,
                'order' => 2,
            ],
            [
                'key' => 'maintenance_mode',
                'label' => 'Maintenance Mode',
                'type' => 'checkbox',
                'required' => false,
                'visible' => true,
                'order' => 3,
            ],
        ];

        foreach ($fields as $field) {
            ProfileSetting::updateOrCreate(
                ['key' => $field['key']],
                $field
            );
        }
    }
}
