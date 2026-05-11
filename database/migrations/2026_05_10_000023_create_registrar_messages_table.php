<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrarMessagesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('registrar_messages')) {
            Schema::create('registrar_messages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('folder', 30)->default('inbox');
                $table->string('sender_name', 190)->default('System');
                $table->string('sender_type', 60)->default('System');
                $table->string('recipient', 190)->nullable();
                $table->string('subject', 190);
                $table->text('body')->nullable();
                $table->string('source_type', 60)->nullable();
                $table->unsignedInteger('source_id')->nullable();
                $table->unsignedInteger('created_by_user_id')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['folder', 'created_at']);
                $table->index(['source_type', 'source_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('registrar_messages');
    }
}
