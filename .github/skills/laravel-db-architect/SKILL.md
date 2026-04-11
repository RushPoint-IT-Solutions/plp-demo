---
name: "laravel-db-architect"
description: "Enforces strict 3NF for all Laravel migrations and seeders. Scans existing schemas to refactor flat tables, implements safe MCP SQL queries, and generates automated test data."
---

# Laravel Database Architect – Strict 3NF Enforcement

## 1. Core Safety Rules (MySQL Parser Guardrails)
When using the MySQL MCP to inspect the database, you MUST avoid 'unquoted dot notation' errors. Use these strict syntaxes:
- **Primary Method:** `SHOW COLUMNS FROM [table_name] IN [database_name]`
- **Secondary Method:** `SELECT * FROM information_schema.columns WHERE table_schema = '[database_name]' AND table_name = '[table_name]'`
- **Fallback:** Use backticks: `` `database`.`table` ``

## 2. Mandatory Pre-Action: Full Schema Audit
Before generating ANY migration, model, or seeder, you must read the current reality of the database:
- Use the MySQL MCP to execute: `SHOW TABLES;`
- Inspect relevant tables using the safe `SHOW COLUMNS` syntax.
- **Never assume** the schema – always read it fresh.

## 3. Strict 3NF Rules (Non‑negotiable)
- **1NF:** No array/JSON columns that store multiple values. Split them into child tables.
- **2NF:** Every non‑key column must depend on the whole primary key (especially for composite keys).
- **3NF:** No transitive dependencies. (e.g., `city` depends on `zip_code`, not on `user_id`. Move `city, state, zip` to a `locations` table).

## 4. Refactoring Existing Tables (The Cleanup Workflow)
When asked to normalize an *existing* database:
1. **Identify Violations:** Scan for repeating groups, lookup values stored as strings, or address clusters.
2. **Data Extraction Migration:** Create a migration to create the new normalized table (e.g., `statuses`).
3. **Data Transfer (Within Migration or Seeder):** Write logic to migrate existing distinct values from the old flat column into the new table.
4. **Structural Alteration:** Create a migration to add the new `foreignId` to the original table, update it to match the new related records, and then `dropColumn` the old flat data.

## 5. New Feature Workflow
1. **Propose a Normalization Plan:** Show the user which tables will be created/altered.
2. **Generate Migrations:** Use `$table->foreignIdFor()` and `constrained()`.
3. **Generate Models:** Define `hasMany`, `belongsTo`, `belongsToMany` relationships.
4. **Generate Factory & Seeder:** Insert at least 50-100 rows. Use `->has()` and `->each()` to automatically populate child/related tables with realistic fake data.
5. **Execution:** Run `php artisan migrate` and `php artisan db:seed`.
6. **Verification:** Run a `JOIN` query via the MySQL MCP using safe syntax to verify relationships, and display the first 5 results.

## 6. Forbidden Patterns
- ❌ `$table->json('tags')` – use a `taggables` polymorphic table instead.
- ❌ `$table->string('status')` – use `$table->foreignId('status_id')->constrained('statuses')`.
- ❌ Flat `address` columns (street, city, state, zip) – create an `addresses` table and reference it.