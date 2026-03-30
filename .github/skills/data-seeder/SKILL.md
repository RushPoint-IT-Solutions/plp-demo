---
name: "data-seeder"
description: "Safe database seeding with production protection."


---

# Skill 09: Data Seeder

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  PRE-SEED SAFETY CHECKLIST (MANDATORY)
Before ANY seeder runs:

1. **Check `.env`**  `APP_ENV` must NOT be `production`.
2. **Confirm Database Name**  prefer databases with `local`, `dev`, `test` or use sqlite for CI.
3. **Query Existing Record Counts**  run `SELECT COUNT(*) FROM <table>;` for key tables.
4. **Run Backup BEFORE Seeding**  e.g. `php artisan backup:run` or export a SQL dump.

##  Seeder Must Be Re-runnable
```php
//  WRONG
Student::create([
    'student_number' => '2024-001',
    'first_name' => 'John',
    // ...
]);

//  CORRECT
Student::updateOrCreate(
    ['student_number' => '2024-001'],
    ['first_name' => 'John', /* ... */]
);
```

##  Production Protection
```php
if (app()->environment('production')) {
    throw new \Exception('Cannot seed in production!');
}
```

##  MCP Integration
- **Filesystem MCP:** Read `.env` to verify `APP_ENV` and database credentials.
- **MySQL MCP:** Run `SELECT COUNT(*)` before and after seeding; run inside a transaction when possible.
- **Backup MCP:** Trigger `php artisan backup:run` (or export SQL) before destructive operations.

##  Escalation Protocol
- If `APP_ENV=production` or production-like database is detected, STOP and output:
  ` ESCALATION REQUIRED. Cannot seed in production. Move to a safe environment or change APP_ENV.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
