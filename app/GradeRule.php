<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class GradeRule extends Model
{
    protected $fillable = [
        'code',
        'grade',
        'remarks',
    ];

    public function periodItems()
    {
        return $this->hasMany(GradeRulePeriod::class)->orderBy('sort_order');
    }

    private function usesNormalizedPeriods()
    {
        return Schema::hasTable('grade_rule_periods');
    }

    private function usesLegacyPeriodsColumn()
    {
        return Schema::hasTable('grade_rules') && Schema::hasColumn('grade_rules', 'periods');
    }

    public function getPeriodsAttribute()
    {
        if ($this->usesNormalizedPeriods()) {
            if ($this->relationLoaded('periodItems')) {
                return $this->periodItems->pluck('period_name')->values()->all();
            }

            return $this->periodItems()->pluck('period_name')->values()->all();
        }

        if ($this->usesLegacyPeriodsColumn()) {
            $rawPeriods = array_key_exists('periods', $this->attributes) ? $this->attributes['periods'] : null;
            if (empty($rawPeriods)) {
                return [];
            }

            $decoded = json_decode($rawPeriods, true);
            if (!is_array($decoded)) {
                return [];
            }

            return collect($decoded)
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->values()
                ->all();
        }

        return [];
    }

    public function syncPeriods(array $periodNames)
    {
        $allowedPeriods = ['Prelim', 'Midterm', 'Final'];

        $cleanNames = collect($periodNames)
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->map(function ($value) {
                return $value === 'Finals' ? 'Final' : $value;
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->filter(function ($value) use ($allowedPeriods) {
                return in_array($value, $allowedPeriods, true);
            })
            ->unique()
            ->values();

        if ($this->usesNormalizedPeriods()) {
            $this->periodItems()->delete();

            foreach ($cleanNames as $index => $name) {
                $this->periodItems()->create([
                    'period_name' => $name,
                    'sort_order' => $index + 1,
                ]);
            }

            return;
        }

        if ($this->usesLegacyPeriodsColumn()) {
            $jsonPeriods = json_encode($cleanNames->all());
            $this->setAttribute('periods', $jsonPeriods);

            if ($this->exists) {
                $this->newQuery()->whereKey($this->getKey())->update([
                    'periods' => $jsonPeriods,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
