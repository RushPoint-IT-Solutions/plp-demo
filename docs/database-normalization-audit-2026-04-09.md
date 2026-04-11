# Database Normalization Audit (2026-04-09)

## Scope
- Database: `plp_demo`
- Audit method: live schema inspection (`information_schema`) + migration/runtime verification
- Base tables reviewed: 69
- Objective: full-table 3NF compliance check, execute the next normalization wave immediately

## Immediate Execution Completed (Wave E)
Applied migration:
- `2026_04_09_000340_wave_e_normalize_student_program_year_dimensions`

### What Wave E normalized
1. Added canonical program and year dimensions to student-centric tables:
- `students`
- `cancellation_waivers`
- `cross_enrollment_requests`
- `master_student_profiles`
- `master_student_grade_files`
- `bed_student_statuses`
- `student_update_runs`

2. Added columns and constraints:
- `course_id` -> FK to `courses.id`
- `year_block_id` -> FK to `year_blocks.id`

3. Backfilled all existing data from legacy text fields:
- `program` or `course` -> `course_id`
- `year_level` -> `year_block_id`

4. Added deterministic catalog coverage for legacy program values by inserting missing canonical courses:
- `BSIS` (`Bachelor of Science in Information Systems`)
- `BSCE` (`Bachelor of Science in Computer Engineering`)

5. Added forward-sync model behavior via trait:
- `app/Concerns/ResolvesProgramYearDimensions.php`
- Wired into affected models so future writes keep FK dimensions populated.

## Post-Migration Validation Evidence
Coverage after Wave E backfill:
- `students`: `41/41` with `course_id`, `41/41` with `year_block_id`
- `cancellation_waivers`: `10/10` with `course_id`, `10/10` with `year_block_id`
- `cross_enrollment_requests`: `10/10` with `course_id`, `10/10` with `year_block_id`
- `master_student_profiles`: `30/30` with `course_id`, `30/30` with `year_block_id`
- `master_student_grade_files`: `30/30` with `course_id`, `30/30` with `year_block_id`

Runtime behavior check:
- Temporary `Student::create(...)` using legacy `program='BSIS'`, `year_level='Second'` auto-resolved to:
  - `course_id=13`
  - `year_block_id=2`

Smoke check:
- Registrar Pre-requisites page still loads correctly after migration (`29` subject rows rendered for BS Entrepreneurship CY `1920`).

## Full-Schema 3NF Status
The database is now materially closer to strict 3NF, but not yet fully strict due to intentional compatibility columns retained in prior waves.

### Remaining non-strict clusters
1. Term duplication cluster (legacy text + FK coexistence):
- Tables still carrying `school_year`/`semester` or `term` alongside `academic_term_id`.

2. Status/type duplication cluster:
- Legacy string columns retained beside lookup FK columns, e.g.:
  - `status` + `status_id`
  - `event_type` + `event_type_id`
  - `announcement_type` + `announcement_type_id`
  - `load_type` + `load_type_id`
  - `application_status` + `application_status_id`
  - `exam_result_status` + `exam_result_status_id`

3. Program/year compatibility leftovers in normalized tables:
- Legacy `program`/`course` and `year_level` text fields remain beside `course_id` and `year_block_id` for backward compatibility.

## Execution Plan for Remaining 3NF Closure
The next waves should be executed as additive-then-drop transitions (safe 3-step normalization):

1. Wave F: Term strictification
- Refactor app reads/writes to rely only on `academic_term_id`.
- Drop legacy `school_year` + `semester`/`term` columns from operational tables after verification.

2. Wave G: Status/type strictification
- Refactor app reads/writes to lookup FK columns.
- Drop legacy text status/type columns once controllers and views no longer depend on them.

3. Wave H: Program/year strictification completion
- Refactor app reads/writes to `course_id` + `year_block_id`.
- Drop legacy text `program`/`course` + `year_level` columns from Wave E tables.

4. Wave I: Applicant/address compatibility cleanup
- Complete remaining conversion paths where raw descriptive fields coexist with normalized references.

## Conclusion
- Request to execute immediate next normalization was completed via Wave E.
- Full database strict-3NF closure is in progress and now reduced to compatibility-column retirement waves (F-I).
