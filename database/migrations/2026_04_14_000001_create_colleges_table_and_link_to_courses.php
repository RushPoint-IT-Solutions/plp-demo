<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCollegesTableAndLinkToCourses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createCollegesTable();
        $this->seedColleges();
        $this->addCollegeIdToCourses();
        $this->addCollegeIdToApplicants();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropCollegeIdFromApplicants();
        $this->dropCollegeIdFromCourses();
        $this->dropCollegesTable();
    }

    private function createCollegesTable()
    {
        if (Schema::hasTable('colleges')) {
            return;
        }

        Schema::create('colleges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 20)->unique();
            $table->string('name', 255)->unique();
            $table->string('abbr', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    private function seedColleges()
    {
        if (!Schema::hasTable('colleges')) {
            return;
        }

        $now = now();

        $colleges = [
            [
                'code' => 'CON',
                'name' => 'COLLEGE OF NURSING',
                'abbr' => 'CON',
                'sort_order' => 1,
            ],
            [
                'code' => 'CAS',
                'name' => 'COLLEGE OF ARTS AND SCIENCES',
                'abbr' => 'CAS',
                'sort_order' => 2,
            ],
            [
                'code' => 'CCS',
                'name' => 'COLLEGE OF COMPUTER STUDIES',
                'abbr' => 'CCS',
                'sort_order' => 3,
            ],
            [
                'code' => 'COE',
                'name' => 'COLLEGE OF ENGINEERING',
                'abbr' => 'COE',
                'sort_order' => 4,
            ],
            [
                'code' => 'CIHM',
                'name' => 'COLLEGE OF INTERNATIONAL HOSPITALITY MANAGEMENT',
                'abbr' => 'CIHM',
                'sort_order' => 5,
            ],
            [
                'code' => 'CBA',
                'name' => 'COLLEGE OF BUSINESS AND ACCOUNTANCY',
                'abbr' => 'CBA',
                'sort_order' => 6,
            ],
            [
                'code' => 'COED',
                'name' => 'COLLEGE OF EDUCATION',
                'abbr' => 'COED',
                'sort_order' => 7,
            ],
        ];

        foreach ($colleges as $college) {
            DB::table('colleges')->updateOrInsert(
                ['code' => $college['code']],
                array_merge($college, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }
    }

    private function addCollegeIdToCourses()
    {
        if (!Schema::hasTable('courses') || Schema::hasColumn('courses', 'college_id')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'program_type')) {
                $table->unsignedBigInteger('college_id')->nullable()->after('program_type');
            } else {
                $table->unsignedBigInteger('college_id')->nullable()->after('id');
            }

            $table->index('college_id', 'courses_college_id_idx');
        });

        $this->backfillCourseCollegeIds();

        if (Schema::hasColumn('courses', 'college_id') && !Schema::hasTable('college_course')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreign('college_id', 'courses_college_id_foreign')
                    ->references('id')
                    ->on('colleges')
                    ->onDelete('set null');
            });
        }
    }

    private function backfillCourseCollegeIds()
    {
        if (!Schema::hasTable('courses')
            || !Schema::hasTable('colleges')
            || !Schema::hasColumn('courses', 'college_id')
            || !Schema::hasColumn('courses', 'department_id')) {
            return;
        }

        $collegeDeptMap = [
            'CON'  => 'NURS',
            'CAS'  => 'AS',
            'CCS'  => 'CS',
            'COE'  => 'ENG',
            'CIHM' => 'IHM',
            'CBA'  => 'BA',
            'COED' => 'ED',
        ];

        foreach ($collegeDeptMap as $collegeCode => $deptKeyword) {
            $collegeId = DB::table('colleges')->where('code', $collegeCode)->value('id');
            if (!$collegeId) {
                continue;
            }

            DB::statement(
                "UPDATE `courses`
                SET `college_id` = ?
                WHERE `college_id` IS NULL
                    AND `department_id` IN (
                        SELECT `id` FROM `departments`
                        WHERE UPPER(`code`) LIKE ? OR UPPER(`description`) LIKE ?
                    )",
                [
                    $collegeId,
                    '%' . strtoupper($deptKeyword) . '%',
                    '%' . strtoupper($deptKeyword) . '%',
                ]
            );
        }

        $anyCollegeId = DB::table('colleges')->orderBy('id')->value('id');
        if ($anyCollegeId) {
            DB::table('courses')
                ->whereNull('college_id')
                ->update(['college_id' => $anyCollegeId]);
        }
    }

    private function addCollegeIdToApplicants()
    {
        if (!Schema::hasTable('applicants') || Schema::hasColumn('applicants', 'college_id')) {
            return;
        }

        $afterColumn = $this->resolveApplicantCollegeAfterColumn();

        Schema::table('applicants', function (Blueprint $table) use ($afterColumn) {
            $column = $table->unsignedBigInteger('college_id')->nullable();

            if ($afterColumn) {
                $column->after($afterColumn);
            }

            $table->index('college_id', 'applicants_college_id_idx');
        });

        if (Schema::hasColumn('applicants', 'college_id') && Schema::hasTable('colleges')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->foreign('college_id', 'applicants_college_id_foreign')
                    ->references('id')
                    ->on('colleges')
                    ->onDelete('set null');
            });
        }
    }

    private function resolveApplicantCollegeAfterColumn()
    {
        $candidates = [
            'application_status_id',
            'exam_result_status_id',
            'id',
        ];

        foreach ($candidates as $candidate) {
            if (Schema::hasColumn('applicants', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function dropCollegeIdFromCourses()
    {
        $tableName = 'courses';
        $foreignName = 'courses_college_id_foreign';

        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'college_id')) {
            return;
        }

        if ($this->foreignKeyExists($tableName, $foreignName)) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                $table->dropForeign($foreignName);
            });
        }

        if ($this->indexExists($tableName, 'courses_college_id_idx')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex('courses_college_id_idx');
            });
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('college_id');
        });
    }

    private function dropCollegeIdFromApplicants()
    {
        $tableName = 'applicants';
        $foreignName = 'applicants_college_id_foreign';

        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'college_id')) {
            return;
        }

        if ($this->foreignKeyExists($tableName, $foreignName)) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                $table->dropForeign($foreignName);
            });
        }

        if ($this->indexExists($tableName, 'applicants_college_id_idx')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex('applicants_college_id_idx');
            });
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('college_id');
        });
    }

    private function dropCollegesTable()
    {
        if (Schema::hasTable('colleges')) {
            Schema::dropIfExists('colleges');
        }
    }

    private function foreignKeyExists($tableName, $foreignName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('constraint_name', $foreignName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists($tableName, $indexName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
}
