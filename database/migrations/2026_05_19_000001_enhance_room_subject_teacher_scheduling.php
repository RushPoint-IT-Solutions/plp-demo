<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnhanceRoomSubjectTeacherScheduling extends Migration
{
    public function up()
    {
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (!Schema::hasColumn('subjects', 'required_room_type')) {
                    $table->string('required_room_type', 80)->nullable()->after('course_type');
                }
                if (!Schema::hasColumn('subjects', 'required_equipment')) {
                    $table->string('required_equipment', 190)->nullable()->after('required_room_type');
                }
                if (!Schema::hasColumn('subjects', 'max_class_size')) {
                    $table->unsignedSmallInteger('max_class_size')->nullable()->after('required_equipment');
                }
            });
        }

        if (Schema::hasTable('faculties')) {
            Schema::table('faculties', function (Blueprint $table) {
                if (!Schema::hasColumn('faculties', 'department')) {
                    $table->string('department', 120)->nullable()->after('name');
                }
                if (!Schema::hasColumn('faculties', 'employment_type')) {
                    $table->string('employment_type', 60)->default('Full-time Teacher')->after('department');
                }
                if (!Schema::hasColumn('faculties', 'max_load_units')) {
                    $table->decimal('max_load_units', 5, 2)->nullable()->after('employment_type');
                }
            });
        }

        if (!Schema::hasTable('room_allowed_subjects')) {
            Schema::create('room_allowed_subjects', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('room_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('assigned_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(['room_id', 'subject_id'], 'ras_room_subject_unique');
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                $table->foreign('assigned_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('teacher_allowed_subjects')) {
            Schema::create('teacher_allowed_subjects', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('faculty_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('assigned_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(['faculty_id', 'subject_id'], 'tas_faculty_subject_unique');
                $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                $table->foreign('assigned_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('teacher_load_settings')) {
            Schema::create('teacher_load_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('employment_type', 60)->unique();
                $table->decimal('max_load_units', 5, 2)->default(24);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('teacher_availability')) {
            Schema::create('teacher_availability', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('faculty_id');
                $table->string('day', 20);
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_available')->default(true);
                $table->timestamps();

                $table->index(['faculty_id', 'day', 'start_time', 'end_time'], 'teacher_availability_time_idx');
                $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('teacher_load_settings')) {
            foreach ([
                ['employment_type' => 'Full-time Teacher', 'max_load_units' => 24],
                ['employment_type' => 'Part-time Teacher', 'max_load_units' => 12],
                ['employment_type' => 'Department Head', 'max_load_units' => 9],
                ['employment_type' => 'Visiting Lecturer', 'max_load_units' => 6],
            ] as $row) {
                DB::table('teacher_load_settings')->updateOrInsert(
                    ['employment_type' => $row['employment_type']],
                    ['max_load_units' => $row['max_load_units'], 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('teacher_availability');
        Schema::dropIfExists('teacher_load_settings');
        Schema::dropIfExists('teacher_allowed_subjects');
        Schema::dropIfExists('room_allowed_subjects');

        if (Schema::hasTable('faculties')) {
            Schema::table('faculties', function (Blueprint $table) {
                foreach (['max_load_units', 'employment_type', 'department'] as $column) {
                    if (Schema::hasColumn('faculties', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                foreach (['max_class_size', 'required_equipment', 'required_room_type'] as $column) {
                    if (Schema::hasColumn('subjects', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
