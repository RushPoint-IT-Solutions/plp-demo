# Full 3NF Rollout Plan (2026-04-08)

## Completion Update (Final Pass)
All planned normalization waves in this rollout are now implemented and migrated:
- Wave A: status/type lookup normalization (initial + expansion)
- Wave B: student profile learning-preference decomposition
- Wave C: canonical academic term dimension and FK rollout
- Wave D: snapshot master-table rationalization
- Address normalization: `location_addresses` dimension and FK linkage

Verification summary:
- `php artisan migrate --force` completed successfully after DB service startup.
- Runtime compatibility checks passed for Wave B accessors and Wave C term FKs.
- Post-change smoke checks passed for:
  - Subject File (single green-themed pager with rows-per-page)
  - Program File
  - Application Process

## Current Reality
The database started partially normalized, then was completed in staged waves to avoid breakage and data loss.

## Strategy
Use phased, additive migrations with backfill and rollback scripts per domain.

## Non-Negotiable Migration Pattern
For every denormalization fix:
1. Create new normalized lookup/child table.
2. Backfill distinct values and connect rows by foreign key.
3. Update code to read/write the FK relation.
4. Validate data consistency.
5. Only then remove old flat columns in a later migration.

## Priority Waves

### Wave A (Low Risk, High Gain)
- Normalize repeated status/type strings into lookup tables:
  - `subjects.grading_status`
  - `system_announcements.announcement_type`
  - `certificates_issued.certificate_type`
  - `report_permissions.report_type`
- Deliverables:
  - lookup tables + FK columns
  - backfill migration
  - model casts/relations and controller updates
Status: Complete (`000270`, `000310`)

### Wave B (Student Profile Decomposition)
- Break multi-valued and mixed-concern columns in `student_profiles` into child tables/pivots:
  - `internet_access`, `it_tools_access`, `devices`, `lms_used`, `lms_preferred`, `lms_reasons`, `preferred_class_time`
- Deliverables:
  - domain lookup tables
  - pivot tables keyed by `student_profile_id`
  - form read/write adapter layer for backward compatibility during transition
Status: Complete (`000280`)

### Wave C (Academic Term Canonicalization)
- Replace duplicated text `school_year` and `semester` usage with FK to canonical dimension table across operational tables.
- Deliverables:
  - canonical term dimension table
  - FK columns in operational tables
  - staged backfill + validation queries
Status: Complete (`000290`)

### Wave D (Legacy Snapshot Rationalization)
- Review snapshot-like tables (`master_student_profiles`, `master_student_grade_files`, `master_faculty_files`).
- Keep as immutable audit snapshots, or replace with derived views if live-write behavior exists.
Status: Complete (`000300`)

### Additional Rollout Item: Address Dimension Normalization
- Introduced `location_addresses` and linked `applicants`/`student_profiles` present/permanent addresses by FK.
Status: Complete (`000320`)

## Guardrails
- No destructive migration in the same release as new FK introduction.
- Every wave must include:
  - rollback SQL in `.security-audits/`
  - count checks before/after backfill
  - page-level smoke tests for affected modules

## Progress Applied in This Task
- Subject File performance hardening to handle high volume safely:
  - server-side pagination and bounded `per_page`
  - index migration for subject-file query path
- Full wave execution completed with migration set through `000320`.
- Compatibility adapters and model traits were added where needed to preserve behavior while normalizing.

## Recommended Execution Order
1. Wave A
2. Wave B
3. Wave C
4. Wave D
5. Address dimension normalization

This sequence has been executed in this rollout.
