<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LinkFacultyDepartmentsToColleges extends Migration
{
    public function up()
    {
        if (Schema::hasTable('departments') && Schema::hasTable('colleges') && !Schema::hasColumn('departments', 'college_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->unsignedBigInteger('college_id')->nullable()->after('description');
                $table->index('college_id', 'departments_college_id_idx');
                $table->foreign('college_id', 'departments_college_id_foreign')
                    ->references('id')
                    ->on('colleges')
                    ->onDelete('set null');
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
                if (Schema::hasTable('departments') && !Schema::hasColumn('faculties', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('max_load_units');
                    $table->index('department_id', 'faculties_department_id_idx');
                    $table->foreign('department_id', 'faculties_department_id_foreign')
                        ->references('id')
                        ->on('departments')
                        ->onDelete('set null');
                }

                if (Schema::hasTable('colleges') && !Schema::hasColumn('faculties', 'college_id')) {
                    $table->unsignedBigInteger('college_id')->nullable()->after('department_id');
                    $table->index('college_id', 'faculties_college_id_idx');
                    $table->foreign('college_id', 'faculties_college_id_foreign')
                        ->references('id')
                        ->on('colleges')
                        ->onDelete('set null');
                }
            });
        }

        $this->backfillDepartmentCollegeLinks();
        $this->backfillFacultyDepartmentLinks();
    }

    public function down()
    {
        if (Schema::hasTable('faculties')) {
            Schema::table('faculties', function (Blueprint $table) {
                if (Schema::hasColumn('faculties', 'college_id')) {
                    $table->dropForeign('faculties_college_id_foreign');
                    $table->dropIndex('faculties_college_id_idx');
                    $table->dropColumn('college_id');
                }

                if (Schema::hasColumn('faculties', 'department_id')) {
                    $table->dropForeign('faculties_department_id_foreign');
                    $table->dropIndex('faculties_department_id_idx');
                    $table->dropColumn('department_id');
                }

                foreach (['max_load_units', 'employment_type', 'department'] as $column) {
                    if (Schema::hasColumn('faculties', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'college_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropForeign('departments_college_id_foreign');
                $table->dropIndex('departments_college_id_idx');
                $table->dropColumn('college_id');
            });
        }
    }

    private function backfillDepartmentCollegeLinks(): void
    {
        if (
            !Schema::hasTable('departments') ||
            !Schema::hasTable('courses') ||
            !Schema::hasColumn('departments', 'college_id') ||
            !Schema::hasColumn('courses', 'department_id') ||
            !Schema::hasColumn('courses', 'college_id')
        ) {
            return;
        }

        $links = DB::table('courses')
            ->select('department_id', DB::raw('MIN(college_id) as college_id'))
            ->whereNotNull('department_id')
            ->whereNotNull('college_id')
            ->groupBy('department_id')
            ->get();

        foreach ($links as $link) {
            DB::table('departments')
                ->where('id', $link->department_id)
                ->whereNull('college_id')
                ->update([
                    'college_id' => $link->college_id,
                    'updated_at' => now(),
                ]);
        }
    }

    private function backfillFacultyDepartmentLinks(): void
    {
        if (
            !Schema::hasTable('faculties') ||
            !Schema::hasTable('departments') ||
            !Schema::hasColumn('faculties', 'department') ||
            !Schema::hasColumn('faculties', 'department_id')
        ) {
            return;
        }

        $departments = DB::table('departments')->get(['id', 'code', 'description', 'college_id']);

        DB::table('faculties')
            ->whereNotNull('department')
            ->whereNull('department_id')
            ->orderBy('id')
            ->get(['id', 'department'])
            ->each(function ($faculty) use ($departments) {
                $departmentText = strtolower(trim((string) $faculty->department));
                if ($departmentText === '') {
                    return;
                }

                $department = $departments->first(function ($row) use ($departmentText) {
                    return strtolower((string) $row->code) === $departmentText
                        || strtolower((string) $row->description) === $departmentText;
                });

                if (!$department) {
                    return;
                }

                $payload = [
                    'department_id' => $department->id,
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('faculties', 'college_id') && $department->college_id) {
                    $payload['college_id'] = $department->college_id;
                }

                DB::table('faculties')->where('id', $faculty->id)->update($payload);
            });
    }
}
