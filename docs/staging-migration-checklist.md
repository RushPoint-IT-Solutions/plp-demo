# Staging Migration Checklist

Purpose
- Prevent staging migration failures caused by schema drift, stale column assumptions, or unsafe destructive changes.

How to use this file
- Update this checklist whenever database/migrations files change.
- Keep the latest checklist entry near the top.

Checklist entry template
- Date:
- Branch:
- Migration files changed:
- php artisan migrate:status result: PASS or FAIL
- Compatibility checks performed:
  - Verified no stale column references in pending migrations
  - Verified Schema::hasTable or Schema::hasColumn guards where needed
  - Verified staged backfill before destructive cleanup
- Destructive operations present: YES or NO
- If YES, explicit approval reference:
- Verification evidence (tests or smoke checks):
- Notes and rollback considerations:

## Latest Entry
- Date: 2026-04-25
- Branch: uwis-michael-merge
- Migration files changed:
  - database/migrations/2026_04_24_000001_create_registrar_form_uploads_table.php
  - database/migrations/2026_04_24_000001b_create_retention_policies_table.php
  - database/migrations/2026_04_24_000002_create_archives_table.php
  - database/migrations/2026_04_24_000003_create_archive_retrieval_requests_table.php
  - database/migrations/2026_04_24_000004_create_archive_disposal_queue_table.php
  - database/migrations/2026_04_24_000005_create_disposal_certificates_table.php
  - database/migrations/2026_04_24_000006_create_archive_access_logs_table.php
  - database/migrations/2026_04_24_000007_create_archive_settings_table.php
  - database/migrations/2026_04_24_000008_create_archive_categories_table.php
  - database/migrations/2026_04_25_000001_create_tor_diploma_document_tables.php
- php artisan migrate:status result: PASS
- Compatibility checks performed:
  - Verified all migrations are additive
  - Verified no existing tables are altered
- Destructive operations present: NO
- If YES, explicit approval reference: N/A
- Verification evidence (tests or smoke checks):
  - php artisan migrate:status returns successfully
- Notes and rollback considerations:
  - Additive only, safe to rollback via dropping the new tables.


- Date: 2026-04-22
- Branch: uwis-michael-merge
- Migration files changed:
  - `database/migrations/2026_04_22_000100_create_audit_trail_tables.php`
- php artisan migrate:status result: PASS (new migration is pending but the status command completed successfully)
- Compatibility checks performed:
  - Verified the audit trail migration is additive only and does not alter existing production tables
  - Verified the audit tables use separate subject and change tables so event data stays normalized
  - Verified the controller writes still pass syntax checks after the audit hooks were added
- Destructive operations present: NO
- If YES, explicit approval reference: N/A
- Verification evidence (tests or smoke checks):
  - `php artisan migrate:status`
  - `php -l app/Http/Controllers/Registrar/RegistrarController.php`
  - `php -l app/Support/AuditTrailRecorder.php`
  - `php -l database/migrations/2026_04_22_000100_create_audit_trail_tables.php`
- Notes and rollback considerations:
  - The rollback path drops only the new audit tables and leaves existing operational data untouched.
  - Keep the audit recorder in sync with any future document-management or Admin Tools write paths that should be captured.
