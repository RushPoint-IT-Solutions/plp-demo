<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedRealCourseFileSubjects extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        $subjects = [
            ['GE 101', 'Understanding the Self', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 102', 'Readings in Philippine History', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 103', 'Mathematics in the Modern World', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 104', 'Purposive Communication', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 105', 'Art Appreciation', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 106', 'Science, Technology and Society', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 107', 'Ethics', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 108', 'The Contemporary World', 3, 0, 3, 3, 'GE', true, false, false],
            ['GE 109', 'Life and Works of Rizal', 3, 0, 3, 3, 'GE', true, false, false],
            ['PE 1', 'Physical Activities Toward Health and Fitness 1', 2, 0, 2, 2, 'Minor', true, false, false],
            ['PE 2', 'Physical Activities Toward Health and Fitness 2', 2, 0, 2, 2, 'Minor', true, false, false],
            ['PE 3', 'Physical Activities Toward Health and Fitness 3', 2, 0, 2, 2, 'Minor', true, false, false],
            ['PE 4', 'Physical Activities Toward Health and Fitness 4', 2, 0, 2, 2, 'Minor', true, false, false],
            ['NSTP 1', 'National Service Training Program 1', 3, 0, 3, 3, 'Minor', true, false, false],
            ['NSTP 2', 'National Service Training Program 2', 3, 0, 3, 3, 'Minor', true, false, false],
            ['IT 101', 'Introduction to Computing', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 102', 'Computer Programming 1', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 103', 'Computer Programming 2', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 104', 'Discrete Mathematics', 3, 0, 3, 3, 'Major', false, true, false],
            ['IT 105', 'Data Structures and Algorithms', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 106', 'Information Management', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 107', 'Object-Oriented Programming', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 108', 'Web Systems and Technologies', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 109', 'Networking 1', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 110', 'Systems Integration and Architecture', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 111', 'Information Assurance and Security 1', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 112', 'Application Development and Emerging Technologies', 2, 1, 3, 5, 'Major', false, false, true],
            ['IT 113', 'Technopreneurship', 3, 0, 3, 3, 'Major', false, true, false],
            ['IT 114', 'Capstone Project 1', 3, 0, 3, 3, 'Major', false, false, true],
            ['IT 115', 'Capstone Project 2', 3, 0, 3, 3, 'Major', false, false, true],
            ['CP 126', 'Capstone Project', 2, 3, 5, 8, 'Major', false, false, true],
            ['CS 101', 'Computer Programming', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 102', 'Discrete Structures 1', 3, 0, 3, 3, 'Major', false, true, false],
            ['CS 103', 'Discrete Structures 2', 3, 0, 3, 3, 'Major', false, true, false],
            ['CS 104', 'Algorithms and Complexity', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 105', 'Architecture and Organization', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 106', 'Operating Systems', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 107', 'Software Engineering 1', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 108', 'Software Engineering 2', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 109', 'Database Systems', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 110', 'Human Computer Interaction', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 111', 'Artificial Intelligence', 2, 1, 3, 5, 'Major', false, false, true],
            ['CS 112', 'Thesis Writing 1', 3, 0, 3, 3, 'Major', false, false, true],
            ['CS 113', 'Thesis Writing 2', 3, 0, 3, 3, 'Major', false, false, true],
            ['MATH 101', 'College Algebra', 3, 0, 3, 3, 'Minor', true, true, false],
            ['MATH 102', 'Plane and Spherical Trigonometry', 3, 0, 3, 3, 'Minor', true, true, false],
            ['STAT 101', 'Elementary Statistics', 3, 0, 3, 3, 'Minor', true, true, false],
            ['ENG 101', 'Study and Thinking Skills in English', 3, 0, 3, 3, 'Minor', true, false, false],
            ['FIL 101', 'Kontekstwalisadong Komunikasyon sa Filipino', 3, 0, 3, 3, 'Minor', true, false, false],
            ['ET111', 'Elective 1', 1, 2, 3, 5, 'Elective', false, true, false],
            ['BUS 101', 'Fundamentals of Accounting', 3, 0, 3, 3, 'Major', false, true, false],
            ['BUS 102', 'Business Finance', 3, 0, 3, 3, 'Major', false, true, false],
            ['BUS 103', 'Principles of Marketing', 3, 0, 3, 3, 'Major', false, true, false],
            ['BUS 104', 'Human Resource Management', 3, 0, 3, 3, 'Major', false, true, false],
            ['EDUC 101', 'The Teaching Profession', 3, 0, 3, 3, 'Major', false, true, false],
            ['EDUC 102', 'Facilitating Learner-Centered Teaching', 3, 0, 3, 3, 'Major', false, true, false],
            ['EDUC 103', 'Assessment of Learning 1', 3, 0, 3, 3, 'Major', false, true, false],
            ['EDUC 104', 'Technology for Teaching and Learning 1', 3, 0, 3, 3, 'Major', false, true, false],
            ['CRIM 101', 'Introduction to Criminology', 3, 0, 3, 3, 'Major', false, true, false],
            ['CRIM 102', 'Law Enforcement Organization and Administration', 3, 0, 3, 3, 'Major', false, true, false],
            ['CRIM 103', 'Criminal Law Book 1', 3, 0, 3, 3, 'Major', false, true, false],
            ['CRIM 104', 'Forensic Photography', 2, 1, 3, 5, 'Major', false, true, false],
        ];

        $now = now();

        foreach ($subjects as $subject) {
            [$code, $name, $lec, $lab, $units, $hours, $courseType, $isCore, $isApplied, $isSpecialized] = $subject;

            $match = ['code' => $code];
            if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
                $match['is_subject_file_record'] = true;
            }

            $payload = [
                'name' => $name,
                'units' => $units,
                'lec' => $lec,
                'lab' => $lab,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('subjects', 'hours')) {
                $payload['hours'] = $hours;
            }

            if (Schema::hasColumn('subjects', 'course_type')) {
                $payload['course_type'] = $courseType;
            }

            if (Schema::hasColumn('subjects', 'is_core')) {
                $payload['is_core'] = $isCore;
            }

            if (Schema::hasColumn('subjects', 'is_applied')) {
                $payload['is_applied'] = $isApplied;
            }

            if (Schema::hasColumn('subjects', 'is_specialized')) {
                $payload['is_specialized'] = $isSpecialized;
            }

            $exists = DB::table('subjects')->where($match)->exists();
            if (!$exists && Schema::hasColumn('subjects', 'created_at')) {
                $payload['created_at'] = $now;
            }

            DB::table('subjects')->updateOrInsert($match, $payload);
        }
    }

    public function down()
    {
        // Keep seeded course records to avoid deleting registrar-maintained data.
    }
}
