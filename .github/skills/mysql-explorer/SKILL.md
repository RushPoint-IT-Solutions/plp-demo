---
name: "laravel-db-architect"
description: "End-to-end Laravel database design: handles 3NF normalization, safe MySQL schema inspection, and automated 'trash data' seeder generation."
---

# Laravel Database Architect & Seeder Skill

## 1. Core Safety Rules (MySQL Parser Guardrails)
To avoid 'unquoted dot notation' errors in the MCP SQL parser, you MUST use these syntaxes when inspecting the existing database:
- **Primary Method:** `SHOW COLUMNS FROM [table_name] IN [database_name]`
- **Secondary Method:** `SELECT * FROM information_schema.columns WHERE table_schema = '[database_name]' AND table_name = '[table_name]'`
- **Fallback:** Use backticks: `` `database`.`table` ``

## 2. The Normalization Workflow (3NF)
Before creating any files, analyze the user's request for normalization needs:
- **1NF:** Ensure no multi-valued attributes (e.g., split "Phone Numbers" into a separate `contacts` table if multiple are provided).
- **2NF:** Remove partial dependencies; ensure all non-key attributes are fully functional on the primary key.
- **3NF:** Eliminate transitive dependencies (e.g., move "City/State" to a `locations` table if an "Address" is provided).

## 3. Implementation Steps

### Step A: Schema Discovery
Use the `mysql-explorer` rules to check for existing tables. 
*Action:* Run `php artisan db:show` or the MCP `query` tool to ensure no naming collisions.

### Step B: Laravel Scaffolding
Generate the following using Artisan commands:
1. **Migration:** `php artisan make:migration create_[name]_table` (Include Foreign Keys).
2. **Model:** `php artisan make:model [Name]` (Define `$fillable` and Relationships: `hasMany`, `belongsTo`).
3. **Controller:** `php artisan make:controller [Name]Controller --resource`.

### Step C: The "Trash Data" Factory
Create a Factory that generates high-volume, realistic testing data:
- Use `fake()` modifiers for variety (e.g., `unique()`, `optional()`).
- *Example:* `'email' => fake()->unique()->safeEmail()`.

### Step D: The Seeder
Create a Seeder that generates at least **50-100 records** by default.
- Use the `count()` method: `[Model]::factory()->count(100)->create();`.
- If relationships exist, use `.each()` to create related 'trash' records automatically.

## 4. Execution & Verification
Once the code is written:
1. Run `php artisan migrate`.
2. Run `php artisan db:seed --class=[Name]Seeder`.
3. Use the `mysql-explorer` safe syntax to verify the data was inserted correctly:
   `SELECT COUNT(*) FROM [table_name] IN [database_name]`

## 5. User Interaction
- Prompt the user: "Should I normalize [Field X] into its own table?"
- Confirm: "How many rows of trash data do you need for testing?" (Default is 50).