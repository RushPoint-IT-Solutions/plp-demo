# Database Normalization Audit (2026-04-08)

## Scope and Method
- Database: `plp_demo`
- Full base-table scan performed via `information_schema` using MCP MySQL tooling.
- Base tables scanned: 47
- Foreign keys currently present after full wave rollout: 65+
- Migration status verified and applied for this task's new normalization migrations.

## 3NF Changes Implemented in This Task

### 1) Faculty Notifications normalized to 3NF
Created new entity structure:
- `notification_types`
  - Lookup table for notification classification (`code`, `name`)
- `portal_notifications`
  - Notification event/master record (`notification_type_id`, `title`, `message`, source metadata)
- `notification_deliveries`
  - Per-recipient delivery state (`user_id`, `delivered_at`, `read_at`, `dismissed_at`)

Why this is 3NF:
- Type metadata is separated from notification events.
- Notification event data is separated from recipient-specific state.
- Recipient state is keyed by notification and user, avoiding repeated status columns inside the event record.

Applied migration:
- `2026_04_08_000200_create_portal_notification_3nf_tables`

Post-migration verification:
- `notification_types`: 2 rows
- `portal_notifications`: 1 row
- `notification_deliveries`: 1 row

### 2) Grade rule periods normalized to child rows
Refactor performed:
- Old design: `grade_rules.periods` stored serialized values in a single column.
- New design: `grade_rule_periods` child table (`grade_rule_id`, `period_name`, `sort_order`).

Why this is 3NF:
- Each period is now an atomic row linked to exactly one grade rule.
- Removes repeating groups from a single `longtext/json` style payload.

Applied migration:
- `2026_04_08_000210_normalize_grade_rule_periods_to_3nf`

Post-migration verification:
- `grade_rule_periods`: 16 rows migrated/available
- `grade_rules` no longer contains the `periods` column.

### 3) Wave B complete: Student profile learning preferences decomposed
Refactor performed:
- New lookup table: `student_profile_option_lookups`
- New child table: `student_profile_option_values`
- Migrated legacy columns into atomic rows by domain:
  - `internet_access`
  - `it_tools_access`
  - `devices`
  - `lms_used`
  - `lms_preferred`
  - `lms_reasons`
  - `preferred_class_time`
- Removed legacy Wave B columns from `student_profiles` after backfill.

Compatibility layer preserved:
- `app/StudentProfile.php` now serves compatibility accessors so legacy reads still work during transition.
- `app/Http/Controllers/Student/StudentController.php` now syncs normalized Wave B values transactionally.

Post-migration verification:
- `student_profile_option_lookups`: 40 rows
- `student_profile_option_values`: 8 rows
- Runtime accessor check:
  - `internet_access` sample output: `'Yes'`
  - `devices` sample output: `'["Laptop","Phone"]'`

### 4) Wave C complete: Canonical academic terms introduced
Refactor performed:
- Created `academic_terms` as canonical term dimension.
- Added `academic_term_id` + FK across 15 operational tables:
  - `alumni_tracker_settings`
  - `applicant_application_preferences`
  - `bed_days`
  - `bed_student_statuses`
  - `cancellation_waivers`
  - `certificates_issued`
  - `cross_enrollment_requests`
  - `grading_components`
  - `grading_periods`
  - `students`
  - `student_update_runs`
  - `subjects`
  - `system_grade_postings`
  - `system_school_semesters`
  - `transmutation_rules`

Application-layer term adapter:
- Added `App\Concerns\ResolvesAcademicTerm` and wired into affected models.
- Models can now auto-resolve `academic_term_id` from `school_year` + `term/semester` on save.

Post-migration verification:
- `academic_terms`: 7 rows
- Runtime sample check:
  - `Student::first()->academic_term_id`: `3`
  - `Subject::first()->academic_term_id`: `3`

### 5) Wave D complete: Snapshot tables rationalized
Refactor performed:
- Added source-link and snapshot metadata fields:
  - `master_student_profiles.source_student_id`
  - `master_student_grade_files.source_student_id`
  - `master_faculty_files.source_faculty_id`
  - `snapshot_taken_at`, `snapshot_note`, `is_snapshot`
- Added FK constraints to source tables where applicable.
- Updated admin-tools create/update flows to populate source IDs and snapshot metadata.

Why this is closer to 3NF:
- Snapshot metadata and source identity are explicitly modeled.
- Duplicated descriptive rows are now tied to source entities for traceability without changing snapshot semantics.

### 6) Wave A expansion complete: Additional status/type lookup normalization
Refactor performed:
- Added lookup tables and FK backfills for:
  - `applicants.application_status -> applicant_application_statuses`
  - `applicants.exam_result_status -> applicant_exam_result_statuses`
  - `academic_calendar_events.event_type -> academic_event_types`
  - `cancellation_waivers.status -> waiver_statuses`
  - `cross_enrollment_requests.status -> cross_enrollment_statuses`
  - `subjects.load_type -> subject_load_types`

Notes:
- Legacy string columns were retained for backward compatibility.
- New FK columns are available and backfilled where source values exist.

### 7) Address dimension normalization complete
Refactor performed:
- Created `location_addresses`.
- Added and backfilled:
  - `applicants.present_location_address_id`
  - `applicants.permanent_location_address_id`
  - `student_profiles.present_location_address_id`
  - `student_profiles.permanent_location_address_id`
- Added FK constraints to `location_addresses`.

Post-migration verification:
- `location_addresses`: 32 rows

## Frontend and Page Regression Checks

Smoke checks completed after full migration run:
- Subject File:
  - Single active paginator verified (`singlePager=1`)
  - Duplicate auto-pagination hidden/removed (`duplicateAutoPagerVisible=0`)
  - Legacy-green styling verified (`rgb(15, 123, 67)`)
  - Row-per-page selector retained.
- Program File:
  - Filtered view reloads without breakage; controls and table render as expected.
- Application Process:
  - Filtered view reloads with records and controls intact.

## Full Migration Set Applied in This Rollout

- `2026_04_08_000200_create_portal_notification_3nf_tables`
- `2026_04_08_000210_normalize_grade_rule_periods_to_3nf`
- `2026_04_08_000240_ensure_program_file_columns_on_courses_table`
- `2026_04_08_000250_ensure_subject_file_columns_on_subjects_table`
- `2026_04_08_000260_add_subject_file_indexes_to_subjects_table`
- `2026_04_08_000270_start_wave_a_status_lookup_backfill`
- `2026_04_08_000280_wave_b_normalize_student_profile_learning_preferences`
- `2026_04_08_000290_wave_c_canonicalize_academic_terms`
- `2026_04_08_000300_wave_d_rationalize_snapshot_master_tables`
- `2026_04_08_000310_wave_a_expand_status_type_lookups`
- `2026_04_08_000320_normalize_address_dimensions`

## Final Status

- Planned normalization waves for this rollout (Wave A/B/C/D + address dimension + prior notification/grade-rule normalization) are complete.
- Application compatibility adapters were added where destructive schema steps were required.
- Key registrar pages were revalidated post-migration and remained functional.

