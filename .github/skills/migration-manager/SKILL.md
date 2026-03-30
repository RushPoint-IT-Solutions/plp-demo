---
name: "migration-manager"
description: "Create and manage Laravel 5.7 migrations safely  one logical change per migration, never edit run migrations, prefer additive changes."


---

# Skill 23: Migration Manager

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.
- **Database:** XAMPP MySQL (development), but migrations are environment-agnostic.

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Best Practices for Migrations

### 1. One Logical Change Per Migration
- Each migration should do **one thing**: create a table, add a column, add an index, etc.
- This makes rollbacks and debugging predictable.

### 2. Never Edit a Migration That Has Been Run on Production
- If `php artisan migrate:status` shows that a migration has been **run** (even in local), create a **new migration** for further changes.
- Editing an alreadyrun migration would require dangerous rollbacks and possible data loss.

### 3. Prefer Extending Pending Migrations
- If a migration for the same table exists and is **still pending** (not yet migrated), the AI may edit that file instead of creating a new one.
- Example: adding a new column to a table that hasn't been created yet  just add it to the original `create_...` migration.

### 4. Write Reversible Migrations
- Always implement a `down()` method that can undo the `up()` changes.
- For destructive changes (dropping columns), store data in a temporary way if needed, or simply document that rollback will lose data.

### 5. Use Laravel Schema Builder  No Raw SQL (Unless Absolutely Necessary)
- Prefer `Schema::create()`, `Schema::table()`, `$table->string()`, etc.
- Raw SQL (`DB::statement`) only for complex indexes, fulltext search, or databasespecific features.

### 6. Add Indexes and Foreign Keys Explicitly
- Index foreign keys: `$table->unsignedInteger('course_id'); $table->foreign('course_id')->references('id')->on('courses');`
- Add indexes on columns used in `WHERE`/`JOIN`: `$table->index('email');`

### 7. Keep Migrations Idempotent
- Use `Schema::hasTable()`, `Schema::hasColumn()`, `Schema::hasIndex()` before adding elements to avoid duplicate errors.

##  Creating a New Migration (CLI)
```bash
# Create a new migration file
php artisan make:migration create_students_table
php artisan make:migration add_phone_number_to_students_table
php artisan make:migration create_enrollments_table --create=enrollments
```

##  AI Workflow for Migration Tasks
When the user asks to create a database table or add a column:
- List existing migration files (using Filesystem MCP) in `database/migrations/`.
- Check which migrations have been run (using MySQL MCP  query the `migrations` table).

Decide action:

- If a migration for the same table exists and has not been run  edit that migration file (add the new column/change to the existing `up()` and `down()`).
- If no relevant pending migration exists  create a new migration with `php artisan make:migration`.
- If the migration has already been run (even in local)  create a new migration (never edit the run one).

- Write the migration code following the best practices above.
- Update the corresponding Model (e.g., add the new column to `$fillable`).
- Notify the user to run `php artisan migrate`.

## Example: Adding a column to an existing table that has already been migrated
```php
// New migration: add_phone_number_to_students_table.php
public function up()
{
    Schema::table('students', function (Blueprint $table) {
        $table->string('phone_number')->nullable()->after('email');
        $table->index('phone_number');
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropIndex(['phone_number']);
        $table->dropColumn('phone_number');
    });
}
```

## Example: Creating a new table (if not exists)
```php
public function up()
{
    if (!Schema::hasTable('courses')) {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }
}
```

##  MCP Integration
Filesystem MCP: List all migration files, read existing migration content.

MySQL MCP: Query the `migrations` table to see which migrations have been run; check table/column existence via `SHOW TABLES` or `DESCRIBE`.

Playwright MCP: (Optional) Not needed for migrations.

##  Escalation Protocol
If the AI attempts to edit a migration that has already been run (according to the `migrations` table), STOP and output:
```
 ESCALATION REQUIRED. Cannot edit an alreadyrun migration. Create a new migration instead.
```

If the AI attempts to drop a table or column without a backup warning, STOP and ask for human confirmation.

##  STOP COMMAND
Output WAITING_FOR_HUMAN_OK when the file is generated.

