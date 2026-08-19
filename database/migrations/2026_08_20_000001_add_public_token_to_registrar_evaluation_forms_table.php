<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPublicTokenToRegistrarEvaluationFormsTable extends Migration
{
    public function up()
    {
        Schema::table('registrar_evaluation_forms', function (Blueprint $table) {
            $table->string('public_token', 40)->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('registrar_evaluation_forms', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
}
