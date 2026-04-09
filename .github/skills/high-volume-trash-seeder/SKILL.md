---
name: "high-volume-trash-seeder"
description: "Generates 5,000+ realistic, localized records using Laravel Model Factories based on UI-to-Seeder mapping. Includes progress bar, memory management, and UI verification via screenshot."
---

# Realistic High-Volume Seeder

## Purpose
Populate the 3NF database with thousands of records that look like real school data to test search, filtering, and server load.

## Logic & Tools
- **Tool:** Laravel **Model Factories** (generated or updated based on `ui-data-contract.json`).
- **Localization:** Set `FAKER_LOCALE=en_PH` in `.env` to get local names and addresses.
- **Realism Rules:**
  - Names: `fake()->name()`
  - Student IDs: `fake()->numerify('2026-####')`
  - Emails: `fake()->unique()->safeEmail()`
  - Addresses: Use `fake()->address()` with Philippine locale.
- **Volume (from `.ultimate-architect.json`):**
  - Applicants: 2,000
  - Students: 1,000 (with Grades/Schedules)
  - Faculty: 100

## Workflow

1) **Generate/Update Model Factories**
- Read `ui-data-contract.json` from `.security-audits/`.
- For each model involved, create or update a Laravel Factory in `database/factories/` that defines all required fields.
- Ensure relationships are properly handled (e.g., a Student belongs to a User, has many Grades).

2) **Create or Update DatabaseSeeder**
- Write a `DatabaseSeeder.php` that calls these factories with the configured counts.
- Use `LazyCollection` or chunking (e.g., `->chunk(100)`) to avoid memory exhaustion when creating thousands of records.

3) **Execute Seeding with Progress Feedback**
- Run `php artisan migrate:fresh --seed` (if destructive mode) or `php artisan db:seed` (if additive).
- Output a progress bar via CLI (e.g., `$this->command->getOutput()->progressStart($count)`).

4) **Verify Seeded Data in UI**
- Use Playwright to navigate to a page that displays the seeded data (e.g., the Student List).
- Take a screenshot and save it to `.security-audits/seeded-ui-screenshot.png`.
- If the screenshot shows an empty table, abort and warn the user.

5) **Mandatory Post-Seed Performance Gate**
- For each page that lists seeded models, verify it does **not** fetch/render all rows at once.
- Enforce server-side pagination (`page`, `per_page`) with hard cap `per_page <= 100`.
- Confirm search and sort run on the backend, not by sorting thousands of rows in the browser.
- Verify at least one supporting index exists for primary filter/order columns used by that page.
- If any list page fails this gate, create the required migration/controller/UI patches before marking the seed task complete.

6) **Performance Evidence Artifact**
- Save a short report to `.security-audits/post-seed-performance-check.txt` containing:
  - affected page URLs
  - API pagination meta sample
  - index names added/verified
  - pass/fail per page

## STOP COMMAND
WAITING_FOR_HUMAN_OK