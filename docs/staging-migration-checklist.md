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
