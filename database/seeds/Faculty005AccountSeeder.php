<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates the login account for FAC-005 (Garcia, Elena).
 *
 * Not registered in DatabaseSeeder — run standalone so it isn't
 * bundled with local demo-data seeding:
 *   php artisan db:seed --class=Faculty005AccountSeeder
 *
 * Generates a random one-time password and forces a reset on first
 * login, so no credential is hardcoded in source control.
 */
class Faculty005AccountSeeder extends Seeder
{
    public function run()
    {
        $faculty = DB::table('faculties')->where('code', 'FAC-005')->first();

        if (!$faculty) {
            $this->command->error('FAC-005 not found in faculties table. Aborting.');
            return;
        }

        $existing = DB::table('users')->where('faculty_id', $faculty->id)->first();
        if ($existing) {
            $this->command->info("FAC-005 already has a user account (username: {$existing->username}). No changes made.");
            return;
        }

        $username = 'faculty.garcia';
        $temporaryPassword = Str::random(16);

        DB::table('users')->insert([
            'name' => $faculty->name,
            'username' => $username,
            'email' => 'elena.garcia@plp.local',
            'password' => Hash::make($temporaryPassword),
            'module' => 'faculty',
            'force_password_reset' => true,
            'faculty_id' => $faculty->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Created account for FAC-005 (username: {$username}).");
        $this->command->info("Temporary password: {$temporaryPassword}");
        $this->command->info('Relay this password to the user through a secure channel — it will not be shown again. They must reset it on first login.');
    }
}
