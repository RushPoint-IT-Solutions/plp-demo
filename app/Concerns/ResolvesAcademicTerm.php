<?php

namespace App\Concerns;

use App\AcademicTerm;
use Illuminate\Support\Facades\Schema;

trait ResolvesAcademicTerm
{
    protected static $academicTermColumnCache = [];

    protected $legacyAcademicTermInputs = [];

    public static function bootResolvesAcademicTerm()
    {
        static::saving(function ($model) {
            if (!$model->hasAcademicTermColumn()) {
                return;
            }

            [$schoolYearColumn, $termColumn] = $model->academicTermSourceColumns();

            $schoolYear = $model->resolveAcademicTermSourceValue('school_year', $schoolYearColumn);
            $term = $model->resolveAcademicTermSourceValue('term', $termColumn);

            if ($schoolYear !== '' && $term !== '') {
                $academicTerm = AcademicTerm::firstOrCreate(
                    ['canonical_key' => $model->academicTermCanonicalKey($schoolYear, $term)],
                    [
                        'school_year' => $schoolYear,
                        'term' => $term,
                    ]
                );

                $model->setAttribute('academic_term_id', $academicTerm->id);
                return;
            }

            $academicTermId = $model->getAttribute('academic_term_id');
            if (!$academicTermId) {
                return;
            }

            $academicTerm = AcademicTerm::find($academicTermId);
            if (!$academicTerm) {
                $model->setAttribute('academic_term_id', null);
                return;
            }

            if ($schoolYear === '' && $schoolYearColumn && $model->hasAcademicTermSourceColumn($schoolYearColumn)) {
                $model->setAttribute($schoolYearColumn, $academicTerm->school_year);
            }

            if ($term === '' && $termColumn && $model->hasAcademicTermSourceColumn($termColumn)) {
                $model->setAttribute($termColumn, $academicTerm->term);
            }
        });
    }

    public function getSchoolYearAttribute($value)
    {
        $normalized = trim((string) $value);
        if ($normalized !== '') {
            return $value;
        }

        return $this->relatedAcademicTermValue('school_year', $value);
    }

    public function setSchoolYearAttribute($value)
    {
        $this->setAcademicTermSourceValue('school_year', $value);
    }

    public function getSemesterAttribute($value)
    {
        $normalized = trim((string) $value);
        if ($normalized !== '') {
            return $value;
        }

        return $this->relatedAcademicTermValue('term', $value);
    }

    public function setSemesterAttribute($value)
    {
        $this->setAcademicTermSourceValue('semester', $value);
    }

    public function getTermAttribute($value)
    {
        $normalized = trim((string) $value);
        if ($normalized !== '') {
            return $value;
        }

        return $this->relatedAcademicTermValue('term', $value);
    }

    public function setTermAttribute($value)
    {
        $this->setAcademicTermSourceValue('term', $value);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    protected function hasAcademicTermColumn()
    {
        $cache = $this->academicTermColumnCache();

        return $cache['academic_term_id'];
    }

    protected function hasAcademicTermSourceColumn($column)
    {
        $cache = $this->academicTermColumnCache();

        return !empty($cache[$column]);
    }

    protected function academicTermColumnCache()
    {
        $tableName = $this->getTable();

        if (!isset(self::$academicTermColumnCache[$tableName])) {
            self::$academicTermColumnCache[$tableName] = [
                'academic_term_id' => Schema::hasColumn($tableName, 'academic_term_id'),
                'school_year' => Schema::hasColumn($tableName, 'school_year'),
                'semester' => Schema::hasColumn($tableName, 'semester'),
                'term' => Schema::hasColumn($tableName, 'term'),
            ];
        }

        return self::$academicTermColumnCache[$tableName];
    }

    protected function supportsAcademicTermResolution()
    {
        $cache = $this->academicTermColumnCache();

        return $cache['academic_term_id'];
    }

    protected function academicTermSourceColumns()
    {
        $cache = $this->academicTermColumnCache();

        if ($cache['semester']) {
            return ['school_year', 'semester'];
        }

        if ($cache['term']) {
            return ['school_year', 'term'];
        }

        return [null, null];
    }

    protected function resolveAcademicTermSourceValue($inputKey, $sourceColumn)
    {
        $value = '';

        if ($sourceColumn && $this->hasAcademicTermSourceColumn($sourceColumn)) {
            $value = trim((string) ($this->attributes[$sourceColumn] ?? ''));
        }

        if ($value === '' && isset($this->legacyAcademicTermInputs[$inputKey])) {
            $value = trim((string) $this->legacyAcademicTermInputs[$inputKey]);
        }

        return $value;
    }

    protected function setAcademicTermSourceValue($preferredColumn, $value)
    {
        $normalized = trim((string) $value);
        $normalized = $normalized === '' ? null : $normalized;

        $targetColumn = $this->academicTermTargetColumn($preferredColumn);
        if ($targetColumn && $this->hasAcademicTermSourceColumn($targetColumn)) {
            $this->attributes[$targetColumn] = $normalized;
        } elseif ($targetColumn) {
            unset($this->attributes[$targetColumn]);
        }

        if ($preferredColumn === 'school_year') {
            $this->legacyAcademicTermInputs['school_year'] = $normalized;
        } else {
            $this->legacyAcademicTermInputs['term'] = $normalized;
        }

        $this->syncAcademicTermFromLegacyInputs();
    }

    protected function academicTermTargetColumn($preferredColumn)
    {
        $cache = $this->academicTermColumnCache();

        if ($preferredColumn === 'school_year') {
            return 'school_year';
        }

        if ($preferredColumn === 'semester') {
            if ($cache['semester']) {
                return 'semester';
            }

            if ($cache['term']) {
                return 'term';
            }
        }

        if ($preferredColumn === 'term') {
            if ($cache['term']) {
                return 'term';
            }

            if ($cache['semester']) {
                return 'semester';
            }
        }

        return null;
    }

    protected function syncAcademicTermFromLegacyInputs()
    {
        if (!$this->hasAcademicTermColumn()) {
            return;
        }

        [$schoolYearColumn, $termColumn] = $this->academicTermSourceColumns();

        $schoolYear = $this->resolveAcademicTermSourceValue('school_year', $schoolYearColumn);
        $term = $this->resolveAcademicTermSourceValue('term', $termColumn);

        if ($schoolYear === '' || $term === '') {
            return;
        }

        $academicTerm = AcademicTerm::firstOrCreate(
            ['canonical_key' => $this->academicTermCanonicalKey($schoolYear, $term)],
            [
                'school_year' => $schoolYear,
                'term' => $term,
            ]
        );

        $this->attributes['academic_term_id'] = (int) $academicTerm->id;
    }

    protected function relatedAcademicTermValue($field, $fallback)
    {
        $academicTerm = $this->relationLoaded('academicTerm')
            ? $this->getRelation('academicTerm')
            : null;

        if (!$academicTerm) {
            $academicTermId = $this->getAttribute('academic_term_id');
            if ($academicTermId) {
                $academicTerm = AcademicTerm::find($academicTermId);
            }
        }

        if ($academicTerm && isset($academicTerm->{$field})) {
            return (string) $academicTerm->{$field};
        }

        return $fallback;
    }

    protected function academicTermCanonicalKey($schoolYear, $term)
    {
        return strtolower(trim((string) $schoolYear) . '|' . trim((string) $term));
    }
}
