# PLP data migration - 6 November 2026

The dataset now ships with the project and loads into MySQL through Laravel migrations. No separate import command or manual upload into storage is required.

## Server deployment

Commit and push the application changes, all three import/data migrations, and the entire `database/data/plp/` directory. This directory is included by Git and contains the three original XLSX exports. On the server, pull the changes, configure MySQL in `.env`, and run:

```bash
php -d memory_limit=512M artisan migrate --force
```

The new migration is `2026_11_06_000002_migrate_legacy_plp_data.php`. It runs after the StudentMaster-source and legacy-import-table migrations. Laravel executes it once. Pulling code alone does not execute migrations; include the command above in deployment.

The migration reads `database/data/plp`, not `storage/app/imports/plp`. It verifies the fixed SHA-256 hashes before writing. Leave the bundled files unchanged. PHP needs `pdo_mysql`, `zip`, `xmlreader`, `simplexml`, and `mbstring`; the PHP user needs read access to the data directory and write access to `storage/app/import-reports`. If database credentials change while configuration is cached, run `php artisan config:clear` first.

## Validation and duplicates

The same validation rules apply automatically: cancelled, invalid, placeholder, duplicate, conflicting, and unmatched records are withheld. Blank grades remain null. Missing students and programs are not invented. Validation establishes consistency, not authenticity.

Existing students are skipped by student number; employees by legacy ID or employee number; and grades by student, school year, semester, and subject code. Equivalent semester labels match. Existing records are never overwritten. Genuinely new grades can be linked to a verified existing student.

The source contains 5,335 students, 662 employees, and 212,933 academic records. Before existing-database checks, 5,284 students, 658 employees, and 151,176 academic rows are eligible. This excludes 39 cancelled students, 12 invalid identities, 4 invalid/duplicate employee entries, and 61,757 academic rows missing from StudentMaster.

The earlier local MySQL import created 1,696 students, 658 employees, and 149,517 academic records. It retained 3,544 existing students and withheld 44 identity conflicts and their 1,659 academic records. Counts can differ on another server depending on its existing data.

## Transactions and verification

All eligible writes run in one transaction. Failures roll back the writes, leaving the migration pending for retry. Source identities prevent duplicates if a successful data load is retried before Laravel records migration completion. The migration refuses automatic rollback so referenced student, employee, and academic data cannot be silently deleted.

Each migration run writes `summary.json` and `issues.csv` under `storage/app/import-reports/migration-2026-11-06-*/`. Reports stay private and out of Git. Import provenance is also saved in MySQL.

To verify saved records and grade values against their sources:

```bash
php -d memory_limit=512M tools/verify_legacy_import.php
```

The result is saved to `storage/app/import-reports/mysql-integrity-verification.json`.

The manual `plp:import-legacy` command remains available for a separate future workflow but is not required to deploy this dataset. Downloads/storage copies are unchanged and are not used by the migration. If a server receives a MySQL dump containing the data and its migration history, the data migration is already applied.
