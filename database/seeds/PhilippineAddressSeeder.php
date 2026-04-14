<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PhilippineAddressSeeder extends Seeder
{
    /**
     * Seed the Philippine address lookup tables from the public JSON file.
     *
     * Run with: php artisan db:seed --class=PhilippineAddressSeeder
     *
     * @return void
     */
    public function run()
    {
        $jsonPath = public_path('js/ph-address.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('ph-address.json not found at ' . $jsonPath);
            return;
        }

        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to parse ph-address.json: ' . json_last_error_msg());
            return;
        }

        $this->seedRegions($data);
        $this->seedProvinces($data);
        $this->seedMunicipalities($data);

        $this->command->info('Philippine address lookup tables seeded successfully.');
    }

    private function seedRegions($data)
    {
        if (!Schema::hasTable('ph_regions')) {
            return;
        }

        $now = now();

        foreach ($data as $region) {
            DB::table('ph_regions')->updateOrInsert(
                ['region_name' => $region['name']],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function seedProvinces($data)
    {
        if (!Schema::hasTable('ph_regions') || !Schema::hasTable('ph_provinces')) {
            return;
        }

        $now = now();

        foreach ($data as $regionData) {
            $regionId = DB::table('ph_regions')
                ->where('region_name', $regionData['name'])
                ->value('id');

            if (!$regionId) {
                continue;
            }

            foreach ($regionData['provinces'] as $province) {
                DB::table('ph_provinces')->updateOrInsert(
                    [
                        'province_name' => $province['name'],
                        'region_id'     => $regionId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    private function seedMunicipalities($data)
    {
        if (!Schema::hasTable('ph_regions')
            || !Schema::hasTable('ph_provinces')
            || !Schema::hasTable('ph_municipalities')) {
            return;
        }

        $now = now();

        foreach ($data as $regionData) {
            $regionId = DB::table('ph_regions')
                ->where('region_name', $regionData['name'])
                ->value('id');

            if (!$regionId) {
                continue;
            }

            foreach ($regionData['provinces'] as $province) {
                $provinceId = DB::table('ph_provinces')
                    ->where('province_name', $province['name'])
                    ->where('region_id', $regionId)
                    ->value('id');

                if (!$provinceId) {
                    continue;
                }

                foreach ($province['cities'] as $cityName) {
                    DB::table('ph_municipalities')->updateOrInsert(
                        [
                            'municipality_name' => $cityName,
                            'province_id'       => $provinceId,
                        ],
                        [
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            }
        }
    }
}
