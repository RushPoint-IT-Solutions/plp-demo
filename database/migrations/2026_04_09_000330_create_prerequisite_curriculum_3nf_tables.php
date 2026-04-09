<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePrerequisiteCurriculum3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('course_curricula')) {
            Schema::create('course_curricula', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('course_id');
                $table->string('curriculum_year_code', 20);
                $table->string('title', 180)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('course_id', 'course_curricula_course_fk')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('cascade');
                $table->unique(['course_id', 'curriculum_year_code'], 'course_curricula_course_year_unique');
            });
        }

        if (!Schema::hasTable('course_curriculum_subjects')) {
            Schema::create('course_curriculum_subjects', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('course_curriculum_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('year_block_id');
                $table->unsignedBigInteger('semester_id');
                $table->decimal('credited_units', 5, 2)->default(0);
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();

                $table->foreign('course_curriculum_id', 'cc_subjects_curriculum_fk')
                    ->references('id')
                    ->on('course_curricula')
                    ->onDelete('cascade');
                $table->foreign('subject_id', 'cc_subjects_subject_fk')
                    ->references('id')
                    ->on('subjects')
                    ->onDelete('cascade');
                $table->foreign('year_block_id', 'cc_subjects_year_block_fk')
                    ->references('id')
                    ->on('year_blocks')
                    ->onDelete('restrict');
                $table->foreign('semester_id', 'cc_subjects_semester_fk')
                    ->references('id')
                    ->on('semesters')
                    ->onDelete('restrict');

                $table->index(
                    ['course_curriculum_id', 'year_block_id', 'semester_id', 'display_order'],
                    'cc_subjects_curriculum_year_sem_order_idx'
                );
                $table->unique(
                    ['course_curriculum_id', 'subject_id', 'year_block_id', 'semester_id'],
                    'cc_subjects_unique_row'
                );
            });
        }

        if (!Schema::hasTable('curriculum_requisite_types')) {
            Schema::create('curriculum_requisite_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 30)->unique();
                $table->string('name', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('curriculum_subject_requisites')) {
            Schema::create('curriculum_subject_requisites', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('course_curriculum_subject_id');
                $table->unsignedBigInteger('requisite_subject_id');
                $table->unsignedBigInteger('curriculum_requisite_type_id');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('course_curriculum_subject_id', 'csr_curriculum_subject_fk')
                    ->references('id')
                    ->on('course_curriculum_subjects')
                    ->onDelete('cascade');
                $table->foreign('requisite_subject_id', 'csr_requisite_subject_fk')
                    ->references('id')
                    ->on('subjects')
                    ->onDelete('cascade');
                $table->foreign('curriculum_requisite_type_id', 'csr_requisite_type_fk')
                    ->references('id')
                    ->on('curriculum_requisite_types')
                    ->onDelete('restrict');

                $table->index(
                    ['course_curriculum_subject_id', 'curriculum_requisite_type_id'],
                    'csr_curriculum_subject_type_idx'
                );
                $table->unique(
                    ['course_curriculum_subject_id', 'requisite_subject_id', 'curriculum_requisite_type_id'],
                    'csr_unique_row'
                );
            });
        }

        $this->seedRequisiteTypes();
        $this->seedEntrepreneurshipCurriculum1920();
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_subject_requisites');
        Schema::dropIfExists('curriculum_requisite_types');
        Schema::dropIfExists('course_curriculum_subjects');
        Schema::dropIfExists('course_curricula');
    }

    private function seedRequisiteTypes()
    {
        $rows = [
            ['code' => 'pre', 'name' => 'Pre-requisite'],
            ['code' => 'co', 'name' => 'Co-requisite'],
            ['code' => 'equivalent', 'name' => 'Equivalent Subject'],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('curriculum_requisite_types')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('curriculum_requisite_types')->insert([
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedEntrepreneurshipCurriculum1920()
    {
        $courseId = $this->resolveEntrepreneurshipCourseId();
        $curriculumId = $this->resolveCourseCurriculumId($courseId, '1920');

        $rows = [
            // 1st Year - First Semester
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'ENG 111', 'name' => 'Introduction to Computing', 'units' => 3],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'FIL 111', 'name' => 'Computer Programming 1', 'units' => 0],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'HUM 111', 'name' => 'Understanding The Self', 'units' => 3],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'MATH 111', 'name' => 'Gender and Society', 'units' => 0],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'MGMT 121', 'name' => 'Readings In The Philippine History', 'units' => 0],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'NAT SCI 111', 'name' => 'Physical Science', 'units' => 3],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'NSTP 111', 'name' => 'Literacy Training Service', 'units' => 3],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'PE 111', 'name' => 'Physical Fitness', 'units' => 2],
            ['year' => '1st Year', 'semester' => 'First Semester', 'code' => 'SOC SCI 111', 'name' => 'General Psychology', 'units' => 3],

            // 2nd Year - First Semester
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'ACCTG 101', 'name' => 'Financial Accounting', 'units' => 3],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'FIL 111', 'name' => 'Business Application', 'units' => 3],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'HUM 111', 'name' => 'Basic Economics w/ Land Reform & Taxation', 'units' => 3],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'MATH 111', 'name' => 'Business Correspondence', 'units' => 3],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'MGMT 121', 'name' => 'Business Opportunities I', 'units' => 3],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'NAT SCI 111', 'name' => 'Basic Finance', 'units' => 3],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'NSTP 111', 'name' => 'Individual/Dual Sports', 'units' => 2],
            ['year' => '2nd Year', 'semester' => 'First Semester', 'code' => 'PE 111', 'name' => 'Business Statistics', 'units' => 3],

            // 3rd Year - First Semester
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'ACCTG 103', 'name' => 'Management Accounting', 'units' => 3],
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'ELECTIVE 101', 'name' => 'Management Accounting', 'units' => 3],
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'ENTREP 104', 'name' => 'Business Plan I', 'units' => 3],
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'HUM 112', 'name' => 'Performing Arts, Music & Dance', 'units' => 3],
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'LAW 111', 'name' => 'Law on Obligations and Contracts', 'units' => 3],
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'SOC SCI 113 A', 'name' => 'Philippine History with Politics and Governance', 'units' => 3],
            ['year' => '3rd Year', 'semester' => 'First Semester', 'code' => 'TAX 111', 'name' => 'Income Taxation', 'units' => 3],

            // 4th Year - First Semester
            ['year' => '4th Year', 'semester' => 'First Semester', 'code' => 'ELECTIVE 104', 'name' => 'Franchising', 'units' => 3],
            ['year' => '4th Year', 'semester' => 'First Semester', 'code' => 'ELECTIVE 105', 'name' => 'E-Commerce', 'units' => 3],
            ['year' => '4th Year', 'semester' => 'First Semester', 'code' => 'ENTREP 106', 'name' => 'Entrepreneurship Integration', 'units' => 3],
            ['year' => '4th Year', 'semester' => 'First Semester', 'code' => 'ENTREP 107', 'name' => 'Business Plan Implementation 1', 'units' => 5],
            ['year' => '4th Year', 'semester' => 'First Semester', 'code' => 'MGT 114A', 'name' => 'Business Policy and Strategy', 'units' => 3],
        ];

        $orderCounter = [];

        foreach ($rows as $row) {
            $yearBlockId = $this->resolveYearBlockId($row['year']);
            $semesterId = $this->resolveSemesterId($row['semester']);
            $subjectId = $this->resolveSubjectId($row['code'], $row['name'], (float) $row['units']);

            $orderKey = $yearBlockId . ':' . $semesterId;
            if (!isset($orderCounter[$orderKey])) {
                $orderCounter[$orderKey] = 0;
            }

            $exists = DB::table('course_curriculum_subjects')
                ->where('course_curriculum_id', $curriculumId)
                ->where('subject_id', $subjectId)
                ->where('year_block_id', $yearBlockId)
                ->where('semester_id', $semesterId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('course_curriculum_subjects')->insert([
                'course_curriculum_id' => $curriculumId,
                'subject_id' => $subjectId,
                'year_block_id' => $yearBlockId,
                'semester_id' => $semesterId,
                'credited_units' => (float) $row['units'],
                'display_order' => $orderCounter[$orderKey],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $orderCounter[$orderKey]++;
        }
    }

    private function resolveEntrepreneurshipCourseId()
    {
        $course = DB::table('courses')
            ->where('code', 'BSENTREP')
            ->orWhere('name', 'Bachelor of Science in Entrepreneurship')
            ->orWhere('description', 'Bachelor of Science in Entrepreneurship')
            ->first();

        if ($course) {
            return (int) $course->id;
        }

        $departmentId = DB::table('departments')->orderBy('id')->value('id');
        if (empty($departmentId)) {
            $departmentId = DB::table('departments')->insertGetId([
                'code' => $this->resolveUniqueDepartmentCode('GEN'),
                'description' => 'General Department',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) DB::table('courses')->insertGetId([
            'code' => $this->resolveUniqueCourseCode('BSENTREP'),
            'name' => 'Bachelor of Science in Entrepreneurship',
            'program_type' => 'Degree',
            'department_id' => (int) $departmentId,
            'description' => 'Bachelor of Science in Entrepreneurship',
            'slots' => 0,
            'track_category' => null,
            'non_filipino' => false,
            'dean_director_id' => null,
            'program_file' => 'Level I Accredited',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveCourseCurriculumId($courseId, $curriculumYearCode)
    {
        $id = DB::table('course_curricula')
            ->where('course_id', (int) $courseId)
            ->where('curriculum_year_code', $curriculumYearCode)
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        return (int) DB::table('course_curricula')->insertGetId([
            'course_id' => (int) $courseId,
            'curriculum_year_code' => $curriculumYearCode,
            'title' => 'BS Entrepreneurship Curriculum ' . $curriculumYearCode,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveYearBlockId($label)
    {
        $id = DB::table('year_blocks')
            ->where('label', $label)
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        return (int) DB::table('year_blocks')->insertGetId([
            'label' => $label,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveSemesterId($name)
    {
        $id = DB::table('semesters')
            ->where('name', $name)
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        return (int) DB::table('semesters')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveSubjectId($code, $name, $units)
    {
        $subject = DB::table('subjects')
            ->where('code', $code)
            ->where('name', $name)
            ->first();

        if ($subject) {
            return (int) $subject->id;
        }

        return (int) DB::table('subjects')->insertGetId([
            'code' => $code,
            'name' => $name,
            'units' => $units,
            'lec' => 0,
            'lab' => 0,
            'grading_status' => 'Open For Encoding',
            'course' => 'BSENTREP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveUniqueDepartmentCode($baseCode)
    {
        $candidate = strtoupper(trim((string) $baseCode));
        if ($candidate === '') {
            $candidate = 'GEN';
        }

        $counter = 1;
        while (DB::table('departments')->where('code', $candidate)->exists()) {
            $counter++;
            $candidate = strtoupper(trim((string) $baseCode)) . $counter;
        }

        return $candidate;
    }

    private function resolveUniqueCourseCode($baseCode)
    {
        $candidate = strtoupper(trim((string) $baseCode));
        if ($candidate === '') {
            $candidate = 'BSE';
        }

        $counter = 1;
        while (DB::table('courses')->where('code', $candidate)->exists()) {
            $counter++;
            $candidate = strtoupper(trim((string) $baseCode)) . $counter;
        }

        return $candidate;
    }
}
