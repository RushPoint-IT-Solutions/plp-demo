<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStudentDiscipline3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_discipline_student_types')) {
            Schema::create('student_discipline_student_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('label', 80)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_discipline_case_types')) {
            Schema::create('student_discipline_case_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('label', 80)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_discipline_action_types')) {
            Schema::create('student_discipline_action_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('label', 80)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_discipline_students')) {
            Schema::create('student_discipline_students', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('course_id')->nullable();
                $table->unsignedBigInteger('student_type_id')->nullable();
                $table->timestamps();

                $table->unique('student_id');
                $table->index(['student_type_id', 'course_id'], 'sds_type_course_idx');

                $table->foreign('student_id')
                    ->references('id')
                    ->on('students')
                    ->onDelete('cascade');

                $table->foreign('course_id')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('set null');

                $table->foreign('student_type_id')
                    ->references('id')
                    ->on('student_discipline_student_types')
                    ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('student_discipline_records')) {
            Schema::create('student_discipline_records', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('discipline_student_id');
                $table->unsignedBigInteger('case_type_id')->nullable();
                $table->unsignedBigInteger('action_type_id')->nullable();
                $table->date('incident_date')->nullable();
                $table->boolean('walk_in')->default(false);
                $table->string('called_by', 120)->nullable();
                $table->text('description')->nullable();
                $table->date('action_date')->nullable();
                $table->string('counselor', 120)->nullable();
                $table->text('remarks')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->string('updated_by', 80)->nullable();
                $table->timestamps();

                $table->index(['discipline_student_id', 'incident_date'], 'sdr_student_incident_idx');
                $table->index(['discipline_student_id', 'is_completed'], 'sdr_student_completed_idx');

                $table->foreign('discipline_student_id')
                    ->references('id')
                    ->on('student_discipline_students')
                    ->onDelete('cascade');

                $table->foreign('case_type_id')
                    ->references('id')
                    ->on('student_discipline_case_types')
                    ->onDelete('set null');

                $table->foreign('action_type_id')
                    ->references('id')
                    ->on('student_discipline_action_types')
                    ->onDelete('set null');
            });
        }

        $this->seedLookupRows();
    }

    public function down()
    {
        if (Schema::hasTable('student_discipline_records')) {
            Schema::drop('student_discipline_records');
        }

        if (Schema::hasTable('student_discipline_students')) {
            Schema::drop('student_discipline_students');
        }

        if (Schema::hasTable('student_discipline_action_types')) {
            Schema::drop('student_discipline_action_types');
        }

        if (Schema::hasTable('student_discipline_case_types')) {
            Schema::drop('student_discipline_case_types');
        }

        if (Schema::hasTable('student_discipline_student_types')) {
            Schema::drop('student_discipline_student_types');
        }
    }

    private function seedLookupRows()
    {
        $now = now();

        $studentTypes = [
            ['code' => 'NEW', 'label' => 'New'],
            ['code' => 'OLD', 'label' => 'Old'],
            ['code' => 'TRANSFEREE', 'label' => 'Transferee'],
        ];

        foreach ($studentTypes as $row) {
            $exists = DB::table('student_discipline_student_types')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('student_discipline_student_types')->insert([
                    'code' => $row['code'],
                    'label' => $row['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $caseTypes = [
            ['code' => 'MINOR_OFFENSE', 'label' => 'Minor Offense'],
            ['code' => 'MAJOR_OFFENSE', 'label' => 'Major Offense'],
            ['code' => 'COUNSELING', 'label' => 'Counseling'],
        ];

        foreach ($caseTypes as $row) {
            $exists = DB::table('student_discipline_case_types')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('student_discipline_case_types')->insert([
                    'code' => $row['code'],
                    'label' => $row['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $actionTypes = [
            ['code' => 'COUNSELING_SESSION', 'label' => 'Counseling Session'],
            ['code' => 'WRITTEN_WARNING', 'label' => 'Written Warning'],
            ['code' => 'PARENT_CONFERENCE', 'label' => 'Parent Conference'],
            ['code' => 'COMMUNITY_SERVICE', 'label' => 'Community Service'],
            ['code' => 'FOLLOW_UP_GUIDANCE', 'label' => 'Follow-up Guidance'],
        ];

        foreach ($actionTypes as $row) {
            $exists = DB::table('student_discipline_action_types')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('student_discipline_action_types')->insert([
                    'code' => $row['code'],
                    'label' => $row['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
