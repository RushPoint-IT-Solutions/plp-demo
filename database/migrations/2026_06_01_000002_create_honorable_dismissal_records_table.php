<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHonorableDismissalRecordsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('honorable_dismissal_records')) {
            return;
        }

        Schema::create('honorable_dismissal_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('hd_no')->unique();
            $table->string('status')->default('for_dismissal');
            $table->date('tagged_at')->nullable();
            $table->date('issued_at')->nullable();
            $table->unsignedBigInteger('tagged_by')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();

            $table->unique('student_id');
            $table->index(['status', 'issued_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('honorable_dismissal_records');
    }
}
