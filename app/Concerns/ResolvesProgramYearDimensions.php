<?php

namespace App\Concerns;

use App\Course;
use App\YearBlock;
use Illuminate\Support\Facades\Schema;

trait ResolvesProgramYearDimensions
{
    protected static $programYearColumnCache = [];

    protected $legacyProgramYearInputs = [];

    public static function bootResolvesProgramYearDimensions()
    {
        static::saving(function ($model) {
            if (!$model->supportsProgramYearResolution()) {
                return;
            }

            $courseSourceColumn = $model->programYearCourseSourceColumn();
            $yearSourceColumn = $model->programYearYearSourceColumn();

            if ($model->programYearColumnCache()['course_id'] && $courseSourceColumn) {
                $model->syncCourseDimension($courseSourceColumn);
            } elseif ($model->programYearColumnCache()['course_id']) {
                $model->syncCourseDimensionFromText($model->legacyProgramYearInput('course'));
            }

            if ($model->programYearColumnCache()['year_block_id'] && $yearSourceColumn) {
                $model->syncYearBlockDimension($yearSourceColumn);
            } elseif ($model->programYearColumnCache()['year_block_id']) {
                $model->syncYearBlockDimensionFromText($model->legacyProgramYearInput('year_level'));
            }
        });
    }

    public function getProgramAttribute($value)
    {
        return $this->resolveLegacyCourseDisplayValue($value);
    }

    public function setProgramAttribute($value)
    {
        $this->setLegacyCourseSourceValue('program', $value);
    }

    public function getCourseAttribute($value)
    {
        return $this->resolveLegacyCourseDisplayValue($value);
    }

    public function setCourseAttribute($value)
    {
        $this->setLegacyCourseSourceValue('course', $value);
    }

    public function getYearLevelAttribute($value)
    {
        $normalized = trim((string) $value);
        if ($normalized !== '') {
            return $value;
        }

        $cache = $this->programYearColumnCache();
        if (!$cache['year_block_id']) {
            return $value;
        }

        $yearBlock = $this->relationLoaded('yearBlock') ? $this->getRelation('yearBlock') : null;
        if (!$yearBlock) {
            $yearBlockId = $this->getAttribute('year_block_id');
            if ($yearBlockId) {
                $yearBlock = YearBlock::query()->find($yearBlockId);
            }
        }

        if ($yearBlock && trim((string) $yearBlock->label) !== '') {
            return (string) $yearBlock->label;
        }

        return $value;
    }

    public function setYearLevelAttribute($value)
    {
        $normalized = trim((string) $value);
        $normalized = $normalized === '' ? null : $normalized;

        $cache = $this->programYearColumnCache();
        if ($cache['year_level']) {
            $this->attributes['year_level'] = $normalized;
        } else {
            unset($this->attributes['year_level']);
        }

        $this->legacyProgramYearInputs['year_level'] = $normalized;

        if (!$cache['year_block_id']) {
            return;
        }

        if ($normalized === null) {
            $this->attributes['year_block_id'] = null;
            return;
        }

        $this->syncYearBlockDimensionFromText($normalized);
    }

    protected function supportsProgramYearResolution()
    {
        $cache = $this->programYearColumnCache();

        $hasCourseSupport = $cache['course_id'];
        $hasYearSupport = $cache['year_block_id'];

        return $hasCourseSupport || $hasYearSupport;
    }

    protected function programYearCourseSourceColumn()
    {
        $cache = $this->programYearColumnCache();

        if ($cache['program']) {
            return 'program';
        }

        if ($cache['course']) {
            return 'course';
        }

        return null;
    }

    protected function programYearYearSourceColumn()
    {
        $cache = $this->programYearColumnCache();

        if ($cache['year_level']) {
            return 'year_level';
        }

        return null;
    }

    protected function syncCourseDimension($sourceColumn)
    {
        $cache = $this->programYearColumnCache();
        if (!$cache['course_id']) {
            return;
        }

        $sourceText = trim((string) ($this->attributes[$sourceColumn] ?? ''));
        $courseId = $this->getAttribute('course_id');

        if ($sourceText !== '' && !$courseId) {
            $this->syncCourseDimensionFromText($sourceText);
            return;
        }

        if ($sourceText === '' && $courseId) {
            $course = Course::query()->find($courseId);
            if ($course) {
                $this->setAttribute($sourceColumn, (string) ($course->code ?: $course->name));
            }
        }
    }

    protected function syncCourseDimensionFromText($sourceText)
    {
        $cache = $this->programYearColumnCache();
        if (!$cache['course_id']) {
            return;
        }

        $sourceText = trim((string) $sourceText);
        if ($sourceText === '') {
            return;
        }

        $normalized = strtolower($sourceText);

        $course = Course::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->orWhereRaw('LOWER(TRIM(code)) = ?', [$normalized])
            ->first();

        if ($course) {
            $this->setAttribute('course_id', (int) $course->id);
        }
    }

    protected function syncYearBlockDimension($sourceColumn)
    {
        $cache = $this->programYearColumnCache();
        if (!$cache['year_block_id']) {
            return;
        }

        $sourceText = trim((string) ($this->attributes[$sourceColumn] ?? ''));
        $yearBlockId = $this->getAttribute('year_block_id');

        if ($sourceText !== '' && !$yearBlockId) {
            $this->syncYearBlockDimensionFromText($sourceText);
            return;
        }

        if ($sourceText === '' && $yearBlockId) {
            $yearBlock = YearBlock::query()->find($yearBlockId);
            if ($yearBlock) {
                $this->setAttribute($sourceColumn, (string) $yearBlock->label);
            }
        }
    }

    protected function syncYearBlockDimensionFromText($sourceText)
    {
        $cache = $this->programYearColumnCache();
        if (!$cache['year_block_id']) {
            return;
        }

        $sourceText = trim((string) $sourceText);
        if ($sourceText === '') {
            return;
        }

        $label = $this->normalizeYearBlockLabel($sourceText);
        if (!$label) {
            return;
        }

        $yearBlock = YearBlock::query()
            ->whereRaw('LOWER(TRIM(label)) = ?', [strtolower($label)])
            ->first();

        if ($yearBlock) {
            $this->setAttribute('year_block_id', (int) $yearBlock->id);
        }
    }

    protected function normalizeYearBlockLabel($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['1', '1st', '1st year', '1st yr', 'first', 'first year'], true)) {
            return '1st Year';
        }

        if (in_array($normalized, ['2', '2nd', '2nd year', '2nd yr', 'second', 'second year'], true)) {
            return '2nd Year';
        }

        if (in_array($normalized, ['3', '3rd', '3rd year', '3rd yr', 'third', 'third year'], true)) {
            return '3rd Year';
        }

        if (in_array($normalized, ['4', '4th', '4th year', '4th yr', '4a', 'fourth', 'fourth year'], true)) {
            return '4th Year';
        }

        if (preg_match('/^1/', $normalized)) {
            return '1st Year';
        }

        if (preg_match('/^2/', $normalized)) {
            return '2nd Year';
        }

        if (preg_match('/^3/', $normalized)) {
            return '3rd Year';
        }

        if (preg_match('/^4/', $normalized)) {
            return '4th Year';
        }

        return null;
    }

    protected function programYearColumnCache()
    {
        $tableName = $this->getTable();

        if (!isset(self::$programYearColumnCache[$tableName])) {
            self::$programYearColumnCache[$tableName] = [
                'course_id' => Schema::hasColumn($tableName, 'course_id'),
                'year_block_id' => Schema::hasColumn($tableName, 'year_block_id'),
                'program' => Schema::hasColumn($tableName, 'program'),
                'course' => Schema::hasColumn($tableName, 'course'),
                'year_level' => Schema::hasColumn($tableName, 'year_level'),
            ];
        }

        return self::$programYearColumnCache[$tableName];
    }

    protected function resolveLegacyCourseDisplayValue($value)
    {
        $normalized = trim((string) $value);
        if ($normalized !== '') {
            return $value;
        }

        $cache = $this->programYearColumnCache();
        if (!$cache['course_id']) {
            return $value;
        }

        $course = $this->relationLoaded('canonicalCourse') ? $this->getRelation('canonicalCourse') : null;
        if (!$course) {
            $courseId = $this->getAttribute('course_id');
            if ($courseId) {
                $course = Course::query()->find($courseId);
            }
        }

        if ($course) {
            return (string) ($course->code ?: $course->name);
        }

        return $value;
    }

    protected function setLegacyCourseSourceValue($sourceColumn, $value)
    {
        $normalized = trim((string) $value);
        $normalized = $normalized === '' ? null : $normalized;

        $cache = $this->programYearColumnCache();
        if (!empty($cache[$sourceColumn])) {
            $this->attributes[$sourceColumn] = $normalized;
        } else {
            unset($this->attributes[$sourceColumn]);
        }

        $this->legacyProgramYearInputs['course'] = $normalized;

        if (!$cache['course_id']) {
            return;
        }

        if ($normalized === null) {
            $this->attributes['course_id'] = null;
            return;
        }

        $this->syncCourseDimensionFromText($normalized);
    }

    protected function legacyProgramYearInput($key)
    {
        if (!isset($this->legacyProgramYearInputs[$key])) {
            return null;
        }

        $value = trim((string) $this->legacyProgramYearInputs[$key]);

        return $value === '' ? null : $value;
    }
}
