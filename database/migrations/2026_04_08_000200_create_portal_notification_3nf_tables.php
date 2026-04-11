<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePortalNotification3nfTables extends Migration
{
    public function up()
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 60)->unique();
            $table->string('name', 120);
            $table->timestamps();
        });

        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('notification_type_id');
            $table->string('title', 180);
            $table->text('message')->nullable();
            $table->string('source_module', 40)->nullable();
            $table->string('source_reference', 100)->nullable();
            $table->string('source_url', 255)->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('notification_type_id', 'pn_notification_type_fk')
                ->references('id')
                ->on('notification_types')
                ->onDelete('restrict');
            $table->foreign('created_by_user_id', 'pn_creator_user_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['source_module', 'source_reference'], 'pn_source_idx');
            $table->index('created_at', 'pn_created_at_idx');
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('portal_notification_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('delivered_at')->useCurrent();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('dismissed_at')->nullable();
            $table->timestamps();

            $table->foreign('portal_notification_id', 'nd_portal_notification_fk')
                ->references('id')
                ->on('portal_notifications')
                ->onDelete('cascade');
            $table->foreign('user_id', 'nd_user_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['portal_notification_id', 'user_id'], 'nd_unique_notification_user');
            $table->index(['user_id', 'dismissed_at'], 'nd_user_dismissed_idx');
            $table->index(['user_id', 'read_at'], 'nd_user_read_idx');
        });

        $this->seedNotificationTypes();
        $this->seedFacultyDemoNotification();
    }

    public function down()
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('portal_notifications');
        Schema::dropIfExists('notification_types');
    }

    private function seedNotificationTypes()
    {
        $now = Carbon::now();

        $types = [
            ['code' => 'EVALUATION_RESULT_SHARED', 'name' => 'Evaluation Result Shared', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SYSTEM_NOTICE', 'name' => 'System Notice', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($types as $type) {
            $exists = DB::table('notification_types')
                ->where('code', $type['code'])
                ->exists();

            if (!$exists) {
                DB::table('notification_types')->insert($type);
            }
        }
    }

    private function seedFacultyDemoNotification()
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $facultyUserIds = DB::table('users')
            ->where('module', 'faculty')
            ->pluck('id');

        if ($facultyUserIds->isEmpty()) {
            return;
        }

        $typeId = DB::table('notification_types')
            ->where('code', 'EVALUATION_RESULT_SHARED')
            ->value('id');

        if (empty($typeId)) {
            return;
        }

        $seedReference = 'seed:registrar-evaluation-result-shared';
        $notificationId = DB::table('portal_notifications')
            ->where('source_reference', $seedReference)
            ->value('id');

        $now = Carbon::now();

        if (empty($notificationId)) {
            $notificationId = DB::table('portal_notifications')->insertGetId([
                'notification_type_id' => $typeId,
                'title' => 'Registrar: New Evaluation Result Shared',
                'message' => 'A new faculty evaluation result is now available for review.',
                'source_module' => 'registrar',
                'source_reference' => $seedReference,
                'source_url' => '/faculty/evaluation',
                'created_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($facultyUserIds as $userId) {
            DB::table('notification_deliveries')->updateOrInsert(
                [
                    'portal_notification_id' => $notificationId,
                    'user_id' => $userId,
                ],
                [
                    'delivered_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
