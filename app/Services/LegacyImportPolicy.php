<?php

declare(strict_types=1);

namespace App\Services;

class LegacyImportPolicy
{
    public function exclusion(array $fields, array $identityFields, array $source = []): ?string
    {
        $values = array_values($fields);
        foreach ($source as $cell) { $values[] = $cell['value']; }
        foreach ($values as $value) {
            if (preg_match('/(?<![\pL\pN])(?:cancel(?:led|ed|lation)?|void(?:ed)?|deleted)(?![\pL\pN])/iu', (string) $value)) {
                return 'cancelled_or_void';
            }
        }
        foreach ($identityFields as $key) {
            if (preg_match('/(?<![\pL\pN])(?:test|testing|dummy|sample|placeholder|system administrator|sysadmin)(?![\pL\pN])/iu', (string) ($fields[$key] ?? ''))
                || (!in_array($key, ['fldMiddleName', 'Middle_Name', 'Employee_Name', 'Position_Code'], true)
                    && in_array(strtoupper(trim((string) ($fields[$key] ?? ''))), ['ADMIN', 'N/A', 'NULL', 'NONE', '-', 'XXX', 'UNKNOWN'], true))) {
                return 'placeholder_or_system_account';
            }
        }
        return null;
    }

    public function key(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }
}
