<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportTicketsAndEmailTemplates extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ticket_no', 40)->unique();
                $table->string('requester_type', 40)->default('Student');
                $table->string('requester_name', 190);
                $table->string('requester_email', 190)->nullable();
                $table->string('category', 80)->default('Inquiry');
                $table->string('subject', 190);
                $table->text('message')->nullable();
                $table->string('priority', 30)->default('Normal');
                $table->string('status', 30)->default('Open');
                $table->unsignedInteger('assigned_to_user_id')->nullable();
                $table->unsignedInteger('created_by_user_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('registrar_email_templates')) {
            Schema::create('registrar_email_templates', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 80)->unique();
                $table->string('name', 150);
                $table->string('audience', 80)->default('Student');
                $table->string('subject', 190);
                $table->text('body');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('updated_by_user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('registrar_email_templates');
        Schema::dropIfExists('support_tickets');
    }
}
