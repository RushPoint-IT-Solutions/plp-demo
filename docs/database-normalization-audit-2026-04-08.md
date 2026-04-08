# Database Normalization Audit (2026-04-08)

## Scope and Method
- Database: `plp_demo`
- Full base-table scan performed via `information_schema` using MCP MySQL tooling.
- Base tables scanned: 47
- Foreign keys currently present: 38
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

## Full-Scan Findings: Remaining 3NF Opportunities

The full schema scan found additional legacy fields still suitable for normalization. High-value targets:

1) `student_profiles` multi-valued text columns
- Columns: `internet_access`, `it_tools_access`, `devices`, `lms_used`, `lms_preferred`, `lms_reasons`, `preferred_class_time`
- Recommendation: split into lookup + pivot tables for each domain.

2) Repeated academic-term attributes across operational tables
- Examples: `semester`, `term`, `year_level` in `students`, `subjects`, `grading_periods`, `grading_components`, `student_update_runs`, and others.
- Recommendation: replace free-text term attributes with FK references to canonical term dimensions where feasible.

3) String status/type/category fields
- Examples: `status`, `grading_status`, `application_status`, `event_type`, `announcement_type`, `report_type`, `certificate_type`.
- Recommendation: use dedicated lookup tables with foreign keys for consistency and constraints.

4) Snapshot/master tables containing duplicated descriptive attributes
- Examples: `master_student_profiles`, `master_student_grade_files`, `master_faculty_files`.
- Recommendation: keep as immutable snapshots only, or refactor into normalized references and derived views.

## Task Outcome Summary
- Full table scan completed.
- New faculty notification backend schema implemented in 3NF and migrated.
- Grade-rule periods normalized to child rows and migrated.
- Application code updated for both notification modal behavior and normalized period handling.
