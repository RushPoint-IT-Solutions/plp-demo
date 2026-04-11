<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrarPreRequisitesFeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_prerequisites_data_returns_paginated_payload()
    {
        $graph = $this->seedPrerequisiteGraph();

        $response = $this->actingAs($this->makeRegistrarUser())->getJson(route(
            'registrar.registrar-menu.academic-master.pre-requisites.data',
            [
                'course_id' => $graph['course_id'],
                'curriculum_year' => $graph['curriculum_year_code'],
                'page' => 1,
                'per_page' => 2,
            ]
        ));

        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'course' => [
                    'id' => $graph['course_id'],
                ],
                'curriculum_year' => $graph['curriculum_year_code'],
                'meta' => [
                    'page' => 1,
                    'per_page' => 2,
                    'total' => 4,
                    'last_page' => 2,
                ],
            ]);
    }

    public function test_prerequisites_subject_update_persists_requisites()
    {
        $graph = $this->seedPrerequisiteGraph();

        $response = $this->actingAs($this->makeRegistrarUser())
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson(route(
                'registrar.registrar-menu.academic-master.pre-requisites.subject.update',
                ['courseCurriculumSubjectId' => $graph['target_curriculum_subject_id']]
            ), [
                'pre_subject_ids' => [$graph['subject_ids'][0]],
                'co_subject_ids' => [$graph['subject_ids'][1]],
                'equivalent_subject_ids' => [$graph['subject_ids'][3]],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
            ]);

        $types = DB::table('curriculum_requisite_types')
            ->whereIn('code', ['pre', 'co', 'equivalent'])
            ->pluck('id', 'code')
            ->all();

        $this->assertDatabaseHas('curriculum_subject_requisites', [
            'course_curriculum_subject_id' => $graph['target_curriculum_subject_id'],
            'requisite_subject_id' => $graph['subject_ids'][0],
            'curriculum_requisite_type_id' => $types['pre'],
        ]);

        $this->assertDatabaseHas('curriculum_subject_requisites', [
            'course_curriculum_subject_id' => $graph['target_curriculum_subject_id'],
            'requisite_subject_id' => $graph['subject_ids'][1],
            'curriculum_requisite_type_id' => $types['co'],
        ]);

        $this->assertDatabaseHas('curriculum_subject_requisites', [
            'course_curriculum_subject_id' => $graph['target_curriculum_subject_id'],
            'requisite_subject_id' => $graph['subject_ids'][3],
            'curriculum_requisite_type_id' => $types['equivalent'],
        ]);
    }

    public function test_prerequisites_list_pdf_download_returns_pdf_response()
    {
        $graph = $this->seedPrerequisiteGraph();

        $response = $this->actingAs($this->makeRegistrarUser())->get(route(
            'registrar.registrar-menu.academic-master.pre-requisites.download',
            [
                'course_id' => $graph['course_id'],
                'curriculum_year' => $graph['curriculum_year_code'],
            ]
        ));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'attachment; filename="pre-requisites-',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_prerequisites_subject_pdf_download_returns_pdf_response()
    {
        $graph = $this->seedPrerequisiteGraph();

        $response = $this->actingAs($this->makeRegistrarUser())->get(route(
            'registrar.registrar-menu.academic-master.pre-requisites.subject.download',
            ['courseCurriculumSubjectId' => $graph['target_curriculum_subject_id']]
        ));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'attachment; filename="subject-config-',
            $response->headers->get('Content-Disposition')
        );
    }

    private function seedPrerequisiteGraph()
    {
        $now = now();
        $suffix = (string) mt_rand(10000, 99999);

        $departmentId = (int) DB::table('departments')->insertGetId([
            'code' => 'TP' . $suffix,
            'description' => 'Test Prereq Department ' . $suffix,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $courseId = (int) DB::table('courses')->insertGetId([
            'code' => 'TPC' . $suffix,
            'name' => 'Test Prereq Course ' . $suffix,
            'program_type' => 'college',
            'department_id' => $departmentId,
            'description' => 'Test Course for Pre-requisites',
            'slots' => 100,
            'track_category' => null,
            'non_filipino' => false,
            'dean_director_id' => null,
            'program_file' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $curriculumYearCode = 'AY-TEST-' . $suffix;
        $curriculumYearId = null;

        if (Schema::hasTable('curriculum_years')) {
            $curriculumYearId = (int) DB::table('curriculum_years')->insertGetId([
                'code' => $curriculumYearCode,
                'label' => 'AY Test ' . $suffix,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $curriculumData = [
            'course_id' => $courseId,
            'curriculum_year_code' => $curriculumYearCode,
            'title' => 'Test Curriculum ' . $suffix,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (!is_null($curriculumYearId) && Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            $curriculumData['curriculum_year_id'] = $curriculumYearId;
        }

        $courseCurriculumId = (int) DB::table('course_curricula')->insertGetId($curriculumData);

        $yearBlockId = $this->firstOrCreateYearBlock('1st Year');
        $semesterId = $this->firstOrCreateSemester('First Semester');

        $subjectIds = [];
        for ($i = 1; $i <= 4; $i++) {
            $subjectIds[] = (int) DB::table('subjects')->insertGetId([
                'code' => 'TPS-' . $suffix . '-' . $i,
                'name' => 'Test Subject ' . $i,
                'is_subject_file_record' => true,
                'units' => 3,
                'lec' => 3,
                'lab' => 0,
                'is_core' => false,
                'is_applied' => false,
                'is_specialized' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $targetCurriculumSubjectId = 0;
        foreach ([0, 1, 2, 3] as $index) {
            $insertedId = (int) DB::table('course_curriculum_subjects')->insertGetId([
                'course_curriculum_id' => $courseCurriculumId,
                'subject_id' => $subjectIds[$index],
                'year_block_id' => $yearBlockId,
                'semester_id' => $semesterId,
                'credited_units' => 3,
                'display_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($index === 2) {
                $targetCurriculumSubjectId = $insertedId;
            }
        }

        DB::table('curriculum_requisite_types')->updateOrInsert(
            ['code' => 'pre'],
            ['name' => 'Pre-requisite', 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('curriculum_requisite_types')->updateOrInsert(
            ['code' => 'co'],
            ['name' => 'Co-requisite', 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('curriculum_requisite_types')->updateOrInsert(
            ['code' => 'equivalent'],
            ['name' => 'Equivalent Subject', 'created_at' => $now, 'updated_at' => $now]
        );

        return [
            'course_id' => $courseId,
            'curriculum_year_code' => $curriculumYearCode,
            'target_curriculum_subject_id' => $targetCurriculumSubjectId,
            'subject_ids' => $subjectIds,
        ];
    }

    private function firstOrCreateYearBlock($label)
    {
        $existingId = DB::table('year_blocks')->where('label', $label)->value('id');
        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('year_blocks')->insertGetId([
            'label' => $label,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function firstOrCreateSemester($name)
    {
        $existingId = DB::table('semesters')->where('name', $name)->value('id');
        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('semesters')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeRegistrarUser()
    {
        return new User([
            'name' => 'Registrar Tester',
            'username' => 'registrar-tester-' . mt_rand(1000, 9999),
            'module' => 'registrar',
            'force_password_reset' => false,
        ]);
    }
}
