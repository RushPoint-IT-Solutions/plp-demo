<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Schema;

trait ResolvesLookupCodeFields
{
    protected static $lookupCodeFieldColumnCache = [];

    protected function hasLookupCodeFieldColumn($column)
    {
        $tableName = $this->getTable();

        if (!isset(self::$lookupCodeFieldColumnCache[$tableName])) {
            self::$lookupCodeFieldColumnCache[$tableName] = [];
        }

        if (!array_key_exists($column, self::$lookupCodeFieldColumnCache[$tableName])) {
            self::$lookupCodeFieldColumnCache[$tableName][$column] = Schema::hasColumn($tableName, $column);
        }

        return self::$lookupCodeFieldColumnCache[$tableName][$column];
    }

    protected function getLookupCodeAttributeValue($column, $relationName, $value)
    {
        $normalized = trim((string) $value);
        if ($normalized !== '') {
            return $value;
        }

        $related = $this->relationLoaded($relationName) ? $this->getRelation($relationName) : null;

        if (!$related && method_exists($this, $relationName)) {
            $related = $this->{$relationName}()->first();
        }

        if (!$related) {
            return $value;
        }

        if (isset($related->code) && trim((string) $related->code) !== '') {
            return (string) $related->code;
        }

        if (isset($related->label) && trim((string) $related->label) !== '') {
            return (string) $related->label;
        }

        return $value;
    }

    protected function setLookupCodeAttributeValue($column, $idColumn, $lookupModelClass, $value)
    {
        $normalized = trim((string) $value);
        $normalized = $normalized === '' ? null : $normalized;

        if ($this->hasLookupCodeFieldColumn($column)) {
            $this->attributes[$column] = $normalized;
        } else {
            unset($this->attributes[$column]);
        }

        if (!$this->hasLookupCodeFieldColumn($idColumn)) {
            return;
        }

        if ($normalized === null) {
            $this->attributes[$idColumn] = null;
            return;
        }

        $lookup = $lookupModelClass::firstOrCreate(
            ['code' => $normalized],
            ['label' => $normalized]
        );

        $this->attributes[$idColumn] = (int) $lookup->id;
    }
}
