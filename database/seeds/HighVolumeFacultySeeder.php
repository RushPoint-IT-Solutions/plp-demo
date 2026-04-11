<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HighVolumeFacultySeeder extends Seeder
{
    public function run()
    {
        $now = now();

        $lastNames = [
            'Reyes', 'Santos', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Ramos', 'Dela Cruz',
            'Bautista', 'Gonzales', 'Fernandez', 'Navarro', 'Aquino', 'Lim', 'Villanueva',
            'Castro', 'Domingo', 'Salazar', 'Mercado', 'Pascual', 'Valdez', 'Ferrer', 'Del Rosario',
            'Tan', 'Ocampo',
        ];

        $firstNames = [
            'Juan', 'Maria', 'Jose', 'Ana', 'Mark', 'Liza', 'Paolo', 'Angela', 'Rafael', 'Carla',
            'Noel', 'Patricia', 'Miguel', 'Camille', 'Renato', 'Ella', 'Bryan', 'Kristine', 'Jerome',
            'Bea',
        ];

        for ($i = 1; $i <= 500; $i++) {
            $code = sprintf('FAC-%03d', $i);

            if ($i === 1) {
                $name = 'Abejo, M.';
            } else {
                $offset = $i - 2;
                $last = $lastNames[$offset % count($lastNames)];
                $first = $firstNames[(int) floor($offset / count($lastNames)) % count($firstNames)];
                $middleInitial = chr(65 + ($offset % 26));
                $name = $last . ', ' . $first . ' ' . $middleInitial . '.';
            }

            DB::table('faculties')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->command->info('HighVolumeFacultySeeder seeded 500 faculty rows (FAC-001 to FAC-500).');
    }
}
