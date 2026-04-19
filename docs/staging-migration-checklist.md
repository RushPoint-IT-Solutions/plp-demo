# Staging Migration Checklist

Purpose
- Prevent staging migration failures caused by schema drift, stale column assumptions, or unsafe destructive changes.

How to use this file
- Update this checklist whenever database/migrations files change.
- Keep the latest checklist entry near the top.

## Latest Checklist Entry
- Date: 2026-04-19
- Branch: uwis-michael-merge
- Migration files changed: database/migrations/2026_04_14_000001_create_colleges_table_and_link_to_courses.php
- php artisan migrate:status result: PASS
- Compatibility checks performed:
  - Verified no stale column references in pending migrations
  - Verified Schema::hasTable or Schema::hasColumn guards where needed
  - Verified staged backfill before destructive cleanup
- Destructive operations present: NO
- If YES, explicit approval reference: N/A
- Verification evidence (tests or smoke checks): php artisan migrate:status; code review of migration, model fillables, and registrar query path
- Notes and rollback considerations: The migration adds a normalized `colleges` lookup plus nullable `college_id` references for `courses` and `applicants`. The local database still shows this migration as not yet run, so staging rollout should include the college migration before any consumer code that depends on the new foreign keys.

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
