---
name: "laravel-db-architect"
description: "Enforces strict 3NF for all Laravel migrations and seeders by reading the entire existing schema and preventing flat table designs."
---

# Laravel Database Architect – Strict 3NF Enforcement

## 1. Mandatory Pre-Action: Full Schema Audit
Before generating ANY migration, model, or seeder:
- Run `php artisan db:show` (if available) OR
- Use the **MySQL MCP** tool `query` to execute: `SHOW TABLES;` then `DESCRIBE each_table;`
- **Never assume** the schema – always read it fresh.

## 2. 3NF Rules (Non‑negotiable)
- **1NF:** No array/JSON columns that store multiple values. Split them into child tables.
- **2NF:** Every non‑key column must depend on the whole primary key (especially for composite keys).
- **3NF:** No transitive dependencies – e.g., `city` depends on `zip_code`, not on `user_id`. Move `city, state, zip` to a `postal_codes` table.

## 3. Automatic Normalization Actions
When adding a new feature:
- Identify **repeating groups** → create a new related table.
- Identify **lookup values** (status, type, category) → create a `xxx_types` or `lookups` table instead of a string column.
- Identify **address-like clusters** → extract into a separate table with a foreign key.

## 4. Implementation Steps (Always Execute)
1. **Propose a Normalization Plan** – Show which tables will be created/altered.
2. **Generate migration** – Use `$table->foreignIdFor()` and `constrained()`.
3. **Generate Model** – Define `hasMany`, `belongsTo`, `belongsToMany` relationships.
4. **Generate Factory** – Use `->has()` to create child records automatically.
5. **Generate Seeder** – Insert at least 100 rows, using `->each()` to populate child tables.
6. **Run** `php artisan migrate` and `php artisan db:seed`.
7. **Verify** – Run a `JOIN` query via MCP `query` tool and show the first 5 results.

## 5. Forbidden Patterns
- ❌ `$table->json('tags')` – use a `taggables` polymorphic table instead.
- ❌ `$table->string('status')` – use `$table->foreignId('status_id')->constrained('statuses')`.
- ❌ Flat `address` columns (street, city, state, zip) – create a `addresses` table and reference it.

## 6. MCP Integration
- Use **MySQL MCP** for all `SHOW COLUMNS` and `SELECT` verification queries.
- Use **DB Graph MCP** to visualise relationships before writing code.