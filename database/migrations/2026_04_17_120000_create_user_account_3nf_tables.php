<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAccount3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_account_types')) {
            Schema::create('user_account_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 50)->unique();
                $table->string('name', 120);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_account_states')) {
            Schema::create('user_account_states', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 30)->unique();
                $table->string('name', 80);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_account_profiles')) {
            Schema::create('user_account_profiles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedBigInteger('user_account_type_id');
                $table->unsignedBigInteger('user_account_state_id');
                $table->boolean('is_sample')->default(false);
                $table->timestamps();

                $table->index('user_account_type_id', 'uap_type_idx');
                $table->index('user_account_state_id', 'uap_state_idx');
                $table->index('is_sample', 'uap_is_sample_idx');

                $table->foreign('user_id', 'uap_user_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('user_account_type_id', 'uap_type_fk')
                    ->references('id')
                    ->on('user_account_types')
                    ->onDelete('restrict');

                $table->foreign('user_account_state_id', 'uap_state_fk')
                    ->references('id')
                    ->on('user_account_states')
                    ->onDelete('restrict');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('user_account_profiles')) {
            Schema::table('user_account_profiles', function (Blueprint $table) {
                $table->dropForeign('uap_user_fk');
                $table->dropForeign('uap_type_fk');
                $table->dropForeign('uap_state_fk');
                $table->dropIndex('uap_type_idx');
                $table->dropIndex('uap_state_idx');
                $table->dropIndex('uap_is_sample_idx');
            });
            Schema::dropIfExists('user_account_profiles');
        }

        Schema::dropIfExists('user_account_states');
        Schema::dropIfExists('user_account_types');
    }
}
