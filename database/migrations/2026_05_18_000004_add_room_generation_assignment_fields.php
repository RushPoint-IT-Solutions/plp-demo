<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoomGenerationAssignmentFields extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                if (!Schema::hasColumn('rooms', 'room_code')) {
                    $table->string('room_code', 40)->nullable()->after('id');
                }
                if (!Schema::hasColumn('rooms', 'room_name')) {
                    $table->string('room_name', 120)->nullable()->after('room_code');
                }
                if (!Schema::hasColumn('rooms', 'room_type')) {
                    $table->string('room_type', 80)->default('Lecture Room')->after('capacity');
                }
                if (!Schema::hasColumn('rooms', 'available_days')) {
                    $table->string('available_days', 40)->default('MTWTHFS')->after('room_type');
                }
                if (!Schema::hasColumn('rooms', 'available_start_time')) {
                    $table->time('available_start_time')->default('07:00:00')->after('available_days');
                }
                if (!Schema::hasColumn('rooms', 'available_end_time')) {
                    $table->time('available_end_time')->default('21:00:00')->after('available_start_time');
                }
                if (!Schema::hasColumn('rooms', 'status')) {
                    $table->string('status', 30)->default('Active')->after('available_end_time');
                }
            });
        }

        if (Schema::hasTable('rooms') && !$this->indexExists('rooms', 'rooms_room_code_unique')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->unique('room_code', 'rooms_room_code_unique');
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (!Schema::hasColumn('subjects', 'required_lecture_room_type')) {
                    $table->string('required_lecture_room_type', 80)->nullable()->after('course_type');
                }
                if (!Schema::hasColumn('subjects', 'required_laboratory_room_type')) {
                    $table->string('required_laboratory_room_type', 80)->nullable()->after('required_lecture_room_type');
                }
                if (!Schema::hasColumn('subjects', 'room_requirement_status')) {
                    $table->string('room_requirement_status', 40)->default('Pending')->after('required_laboratory_room_type');
                }
            });
        }

        if (!Schema::hasTable('class_room_assignments')) {
            Schema::create('class_room_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('class_offering_id');
                $table->string('course_code', 80)->nullable();
                $table->string('section_id', 120)->nullable();
                $table->unsignedBigInteger('room_id')->nullable();
                $table->string('room_type_required', 80)->nullable();
                $table->string('schedule_component_type', 30)->default('Lecture');
                $table->string('academic_year', 30)->nullable();
                $table->string('semester', 40)->nullable();
                $table->string('day', 20)->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('assignment_status', 60)->default('Pending Room Assignment');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('class_offering_id', 'cra_subject_fk')
                    ->references('id')
                    ->on('subjects')
                    ->onDelete('cascade');
                $table->foreign('room_id', 'cra_room_fk')
                    ->references('id')
                    ->on('rooms')
                    ->onDelete('set null');
                $table->foreign('created_by', 'cra_created_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->index(['academic_year', 'semester', 'section_id'], 'cra_term_section_idx');
                $table->index(['room_id', 'day', 'start_time', 'end_time'], 'cra_room_time_idx');
                $table->unique(['class_offering_id', 'schedule_component_type'], 'cra_offering_component_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('class_room_assignments');

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                foreach (['room_requirement_status', 'required_laboratory_room_type', 'required_lecture_room_type'] as $column) {
                    if (Schema::hasColumn('subjects', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                if ($this->indexExists('rooms', 'rooms_room_code_unique')) {
                    $table->dropUnique('rooms_room_code_unique');
                }
                foreach (['status', 'available_end_time', 'available_start_time', 'available_days', 'room_type', 'room_name', 'room_code'] as $column) {
                    if (Schema::hasColumn('rooms', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(1) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
}
