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
- Date: 2026-04-21
- Branch: uwis-michael-merge
- Migration files changed:
  - `database/migrations/2026_04_21_000001_add_applicant_search_indexes.php`
- php artisan migrate:status result: PASS (command executed before and after the local targeted migration run)
- Compatibility checks performed:
  - Verified the new migration is additive only and references only existing applicant lookup columns
  - Verified the search query remains compatible with existing applicant rows and now uses index-merge access paths
  - Verified the migration drops only its own indexes in `down()` and does not touch live data columns
- Destructive operations present: NO
- If YES, explicit approval reference: N/A
- Verification evidence (tests or smoke checks):
  - `php artisan migrate:status`
  - `php artisan migrate --path=database/migrations/2026_04_21_000001_add_applicant_search_indexes.php --force`
  - `EXPLAIN` on the application-process applicant search query now returns `index_merge`
  - `php -l app/Http/Controllers/Registrar/RegistrarController.php`
  - `php -l database/migrations/2026_04_21_000001_add_applicant_search_indexes.php`
- Notes and rollback considerations:
  - The rollback path removes only the new applicant search lookup indexes and leaves applicant data intact.
  - Keep the new search-index migration aligned with any future applicant name lookup changes so the query planner stays on indexed paths.
