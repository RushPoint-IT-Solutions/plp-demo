# Database Normalization Audit (2026-04-07)

## Scope
- Schema inspected: `plp_demo`
- Tables found: 41
- Migration state verified up to: `2026_04_07_000110_create_student_profile_images_table` (batch 4)

## Implemented in this task
- Added normalized table: `student_profile_images`
  - 1:1 with `student_profiles` via `student_profile_id` (unique FK)
  - Stores upload metadata (`original_filename`, `storage_path`, `mime_type`, `size_bytes`, uploader user id)
  - Supports registrar batch upload process and uploaded file listing

## High-priority normalization opportunities

### 1) `student_profiles` has multi-valued attributes in text columns
Observed columns:
- `devices`
- `internet_access`
- `it_tools_access`
- `lms_used`
- `lms_preferred`
- `lms_reasons`
- `preferred_class_time`

Why this matters:
- These likely store comma-separated or JSON-like values, which weakens 1NF and queryability.

Suggested normalization:
- Create lookup + pivot tables, e.g.:
  - `device_options`, `student_profile_devices`
  - `lms_options`, `student_profile_lms_used`
  - `internet_access_options`, `student_profile_internet_access`

### 2) Parent/guardian data is column-heavy and repeated
Observed tables:
- `student_profiles`
- `applicant_family_backgrounds`

Why this matters:
- Repeated parent/guardian column groups increase update anomalies and schema rigidity.

Suggested normalization:
- Introduce a shared contact table pattern:
  - `student_profile_contacts` (`student_profile_id`, `contact_type`, name/contact fields)
  - `applicant_contacts` (`applicant_id`, `contact_type`, ...)
- Keep current columns for compatibility, migrate gradually.

### 3) Academic term attributes are duplicated across many tables
Observed repeated columns across multiple tables:
- `school_year`, `semester`, `year_level`, `course`, `college`

Why this matters:
- This creates transitive dependency risk and inconsistent values over time.

Suggested normalization:
- Strengthen reference model using IDs instead of free text where possible:
  - `system_school_semesters` as a canonical term dimension
  - FKs from operational tables to canonical term/program entities
- Add lookup tables for `year_level` values if used as constrained categories.

### 4) Snapshot/master tables duplicate identity + classification fields
Observed tables:
- `master_student_profiles`
- `master_student_grade_files`

Why this matters:
- They store fields already present in core student entities.

Suggested approach:
- If these are true snapshots, keep them but document snapshot intent.
- If they are used as active master records, consider replacing with FK links to `students` + derived views.

### 5) `grade_rules.periods` is `longtext`
Observed:
- `grade_rules.periods` likely stores serialized period structures.

Why this matters:
- Hard to enforce integrity and aggregate by period attributes.

Suggested normalization:
- Split into child rows (`grade_rule_periods`) with explicit columns (name, weight, order).

## Medium-priority consistency opportunities
- Convert string category fields to lookup/FK where practical (`year_level`, status labels, track categories).
- Consider optional historical table for profile photo versions if audit history is required:
  - `student_profile_image_versions` (1:N from `student_profile_images`).

## Notes
- During the final audit pass, direct MySQL access became unavailable (`ECONNREFUSED 127.0.0.1:3306`), so recommendations above are based on completed schema metadata queries gathered earlier in this task.
