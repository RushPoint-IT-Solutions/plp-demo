<?php

use App\StudentProfile;
use App\StudentProfileImage;
use Illuminate\Database\Seeder;

class StudentProfileImageSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Cannot seed in production!');
        }

        $profiles = StudentProfile::query()
            ->whereNotNull('student_no')
            ->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($profiles as $profile) {
            StudentProfileImage::updateOrCreate(
                ['student_profile_id' => $profile->id],
                [
                    'uploaded_by_user_id' => null,
                    'original_filename' => $profile->student_no . '.jpg',
                    'storage_disk' => 'public',
                    'storage_path' => 'students/profile-photos/seed-' . $profile->student_no . '.jpg',
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => 180000,
                ]
            );
        }

        $this->command->info('Student profile image records seeded: ' . $profiles->count());
    }
}
