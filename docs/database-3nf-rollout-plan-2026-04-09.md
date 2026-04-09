# Full 3NF Rollout Plan (2026-04-09)

## Current Status
Implemented waves:
- Wave A: status/type lookup normalization (initial + expansion)
- Wave B: student profile learning-preference decomposition
- Wave C: canonical academic term dimension (`academic_term_id`)
- Wave D: snapshot rationalization
- Wave E: student program/year dimensions (`course_id`, `year_block_id`)

Latest migration applied:
- `2026_04_09_000340_wave_e_normalize_student_program_year_dimensions`

## What Is Still Blocking Strict 3NF
The remaining blockers are mostly compatibility columns retained to avoid regressions:
- Legacy text term columns still coexisting with `academic_term_id`
- Legacy status/type text columns still coexisting with lookup FK IDs
- Legacy program/year text columns still coexisting with `course_id` and `year_block_id`

## Execution Plan (Immediate Next Waves)

### Wave F: Academic Term Strictification
Goal:
- Remove legacy `school_year` + `semester`/`term` text duplication from operational tables.

Actions:
1. Update controllers/services to use `academic_term_id` as source of truth.
2. Keep compatibility accessors where needed for UI labels.
3. Add migration to drop legacy term columns after usage scan passes.
4. Revalidate registrar/student/faculty pages that display term labels.

### Wave G: Status/Type Strictification
Goal:
- Remove legacy text status/type columns after full FK adoption.

Actions:
1. Replace all write paths to set only `*_id` columns.
2. Replace read paths to join lookup tables for labels.
3. Drop text columns such as `status`, `event_type`, `announcement_type`, `load_type`, etc.
4. Re-run API/UI smoke tests for all affected modules.

### Wave H: Program/Year Strictification Completion
Goal:
- Finalize Wave E by removing text `program`/`course` and `year_level` where normalized IDs are now authoritative.

Actions:
1. Refactor remaining query filters from text columns to FK columns.
2. Add compatibility accessors for historical exports if needed.
3. Drop text columns from Wave E tables.
4. Revalidate registrar reports and student list workflows.

### Wave I: Applicant Compatibility Cleanup
Goal:
- Retire remaining duplicated applicant fields where FK replacements exist.

Actions:
1. Migrate app reads/writes to `application_status_id` and `exam_result_status_id` only.
2. Retire old text status fields after verification.
3. Ensure address reads prefer `*_location_address_id` references.

## Safety Rules for Remaining Waves
1. Additive first, destructive second.
2. Backfill before dropping.
3. Keep one logical change per migration.
4. Run page smoke checks after each wave.
5. Generate rollback SQL artifact for each destructive wave.
