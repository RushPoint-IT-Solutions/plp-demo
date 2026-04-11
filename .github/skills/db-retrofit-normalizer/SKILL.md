---
name: "db-retrofit-normalizer"
description: "Safely analyzes existing databases and refactors them to strict 3NF. Reads migration files for drift detection, uses a 3-step migration process to preserve data, and generates automatic rollback scripts."
---

# Database Retrofitter & Safe Normalizer

## Purpose
Analyze an existing, populated Laravel database. Identify 1NF, 2NF, and 3NF violations (e.g., flat address columns, comma-separated JSON tags, redundant string statuses), and safely refactor them without losing user data.

## Required MCP Tools
- MySQL/PostgreSQL MCP (to read schemas and execute data transfers)
- CLI / Terminal (to run `php artisan make:migration`)
- Filesystem (to read migration files, update Eloquent Models)

## Workflow (Steps 0–6)

0) **Migration Inventory & Drift Detection (Optional Audit)**
- Read all files in `database/migrations/` and parse the `up()` methods to build an expected schema.
- Compare the expected schema from migrations with the actual live database schema.
- If discrepancies exist, warn the user and offer the choice to continue based on live schema or abort.
- Save the drift report to `.security-audits/schema-drift-report.txt`.

1) **Schema Audit & Proposal**
- Read the existing schema using safe SQL (`SHOW TABLES`, `SHOW COLUMNS FROM x`).
- Identify flat structures (e.g., `users.street`, `users.city`, `users.zip` should be in an `addresses` table).
- Present a "Refactoring Plan" to the user detailing which new tables will be created.

2) **The 3-Step Safe Migration (CRITICAL)**
When executing a normalization, you MUST generate migrations that follow this exact sequence to prevent data loss:
- **Step A (Up):** Create the new normalized table (e.g., `locations`).
- **Step B (Up):** Use `DB::table()->insert()` or `DB::statement()` inside the migration to extract distinct values from the old table and insert them into the new table. Add the new `foreign_id` to the old table and update the rows to link to the new records.
- **Step C (Up):** ONLY after data is successfully linked, use `$table->dropColumn()` to remove the old flat columns.

3) **Model Updating & Relationship Sync**
- Use `edit_file` to update the affected Models.
- Remove old flat attributes from `$fillable`.
- Add the new relationship methods (`belongsTo`, `hasMany`).
- Ensure foreign key constraints are enforced (`onDelete('cascade')` where appropriate).

4) **Rollback Script Generation**
- Automatically generate a companion SQL rollback file saved to `.security-audits/rollback_YYYYMMDD_HHMMSS.sql`.
- The rollback script should contain the reverse operations to restore the original flat columns and drop the new tables if needed.

5) **3NF Validation Checklist**
- Run SQL queries to verify:
  - No composite primary key violations (2NF).
  - No transitive dependencies (3NF).
  - All foreign key constraints are properly indexed.
- Save validation results to `.security-audits/3nf-validation-report.txt`.

6) **Data Verification**
- Run a `JOIN` query via the Database MCP to verify that the original data is still intact and properly linked across the newly normalized tables.

## STOP COMMAND
When normalization is verified and rollback generated, output exactly:
WAITING_FOR_HUMAN_OK