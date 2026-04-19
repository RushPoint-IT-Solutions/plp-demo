# Global Database 3NF Audit

Purpose
- Keep 3NF verification global to the whole database, not limited to the table touched by a ticket.

How to use this file
1. For every DB-related ticket, run a whole-schema audit pass.
2. Update the latest audit block below in the same change set.
3. If violations are found, either fix them in scope or log a follow-up item with owner and target date.

## Latest Audit Block (Update Per DB Task)
- Audit date: 2026-04-19
- Auditor: Copilot
- Branch: uwis-michael-merge
- Triggering ticket/task: College normalization migration sync
- Total tables reviewed: 4 schema touchpoints reviewed statically; full live-schema inventory pending

### Whole-Schema Findings
| Area | Status (Pass/Issue) | Notes |
|---|---|---|
| 1NF (atomic values, no repeated groups) | Pass | `colleges` stores atomic lookup fields, and dependent tables reference the lookup by key instead of repeating college text. |
| 2NF (full dependency on PK) | Pass | `college_id` is a direct foreign key on `courses` and `applicants`; the existing model fillables already align with the normalized key. |
| 3NF (no transitive dependencies) | Pass | College metadata is isolated in `colleges`, while `courses` and `applicants` keep only the key reference. |
| Lookup normalization | Pass | College code, name, and abbreviation are centralized in the new lookup table and reused through joins. |
| FK integrity and indexing | Pass | The migration adds `college_id` indexes and conditional foreign keys for the dependent tables. |

### Violations or Follow-Ups
| Table | Violation Type | Action | Owner | Target Date |
|---|---|---|---|---|
| ALL_TABLES_BASELINE | Live-schema inventory not executed in this environment | Re-run the full schema inventory on a connected MySQL instance before the next DB-heavy release if a deeper audit trail is required | Team | Next DB-related ticket |

### Verification Evidence
- Migration diff: `database/migrations/2026_04_14_000001_create_colleges_table_and_link_to_courses.php`
- Existing model wiring: `app/College.php`, `app/Course.php`, `app/Applicant.php`
- Registrar query path already joins `colleges` for college-aware slot monitoring when the table and column exist.
- Live DB connection was unavailable in this session, so this audit is code-path based rather than a live schema inventory.

## Historical Audits
- Keep prior audit blocks below this section in reverse chronological order.
