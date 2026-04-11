<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeGradeRulePeriodsTo3nf extends Migration
{
    public function up()
    {
        Schema::create('grade_rule_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('grade_rule_id');
            $table->string('period_name', 50);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('grade_rule_id', 'grp_grade_rule_fk')
                ->references('id')
                ->on('grade_rules')
                ->onDelete('cascade');

            $table->unique(['grade_rule_id', 'period_name'], 'grp_unique_rule_period');
            $table->index(['grade_rule_id', 'sort_order'], 'grp_rule_sort_idx');
        });

        $this->migrateLegacyPeriodsToRows();

        if (Schema::hasColumn('grade_rules', 'periods')) {
            Schema::table('grade_rules', function (Blueprint $table) {
                $table->dropColumn('periods');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasColumn('grade_rules', 'periods')) {
            Schema::table('grade_rules', function (Blueprint $table) {
                $table->json('periods')->nullable()->after('remarks');
            });
        }

        $rules = DB::table('grade_rules')->select('id')->get();
        foreach ($rules as $rule) {
            $periods = DB::table('grade_rule_periods')
                ->where('grade_rule_id', $rule->id)
                ->orderBy('sort_order')
                ->pluck('period_name')
                ->values()
                ->all();

            DB::table('grade_rules')
                ->where('id', $rule->id)
                ->update([
                    'periods' => empty($periods) ? null : json_encode($periods),
                    'updated_at' => Carbon::now(),
                ]);
        }

        Schema::dropIfExists('grade_rule_periods');
    }

    private function migrateLegacyPeriodsToRows()
    {
        if (!Schema::hasTable('grade_rules') || !Schema::hasColumn('grade_rules', 'periods')) {
            return;
        }

        $rules = DB::table('grade_rules')
            ->select('id', 'periods')
            ->orderBy('id')
            ->get();

        $now = Carbon::now();

        foreach ($rules as $rule) {
            if (empty($rule->periods)) {
                continue;
            }

            $decoded = json_decode($rule->periods, true);
            if (!is_array($decoded)) {
                continue;
            }

            $sortOrder = 1;
            foreach ($decoded as $periodName) {
                $periodName = trim((string) $periodName);
                if ($periodName === '') {
                    continue;
                }

                $exists = DB::table('grade_rule_periods')
                    ->where('grade_rule_id', $rule->id)
                    ->where('period_name', $periodName)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('grade_rule_periods')->insert([
                    'grade_rule_id' => $rule->id,
                    'period_name' => $periodName,
                    'sort_order' => $sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $sortOrder++;
            }
        }
    }
}
