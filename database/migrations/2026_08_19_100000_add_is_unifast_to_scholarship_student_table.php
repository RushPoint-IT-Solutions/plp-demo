<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsUnifastToScholarshipStudentTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('scholarship_student') && !Schema::hasColumn('scholarship_student', 'is_unifast')) {
            Schema::table('scholarship_student', function (Blueprint $table) {
                $table->boolean('is_unifast')->default(false)->after('scholarship_program_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('scholarship_student') && Schema::hasColumn('scholarship_student', 'is_unifast')) {
            Schema::table('scholarship_student', function (Blueprint $table) {
                $table->dropColumn('is_unifast');
            });
        }
    }
}
