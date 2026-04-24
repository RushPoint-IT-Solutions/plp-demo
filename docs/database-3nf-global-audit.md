# Global Database 3NF Audit

Purpose
- Keep 3NF verification global to the whole database, not limited to the table touched by a ticket.

How to use this file
1. For every DB-related ticket, run a whole-schema audit pass.
2. Update the latest audit block below in the same change set.
3. If violations are found, either fix them in scope or log a follow-up item with owner and target date.

## Latest Audit Block (Update Per DB Task)
- Audit date: 2026-04-25
- Auditor: Copilot
- Branch: uwis-michael-merge
- Triggering ticket/task: Registrar Forms Archive and Document Management
- Total tables reviewed: Whole-schema migration inventory reviewed via php artisan migrate:status; targeted relation checks completed for archive and document models

### Whole-Schema Findings
| Area | Status (Pass/Issue) | Notes |
|---|---|---|
| 1NF (atomic values, no repeated groups) | Pass | The new tables are fully 1NF. |
| 2NF (full dependency on PK) | Pass | New tables for archives and form uploads depend fully on their primary keys. |
| 3NF (no transitive dependencies) | Pass | No transitive dependencies. Document types, amendment reasons, and retention policies are normalized into separate tables. |
| Lookup normalization | Pass | Added lookup tables for archive categories and document types. |
| FK integrity and indexing | Pass | Foreign keys are established properly between archive models, retrieval requests, and logs. |

### Violations or Follow-Ups
| Table | Violation Type | Action | Owner | Target Date |
|---|---|---|---|---|
| None | No new normalization issues introduced | No follow-up required | Team | N/A |

### Verification Evidence
- Schema inventory command/output reference: php artisan migrate:status run before and after applying new migrations.
- Applied migration commands: successfully migrated multiple additive tables for the archive and form uploads.
- Query-plan validation: Indexes are added on standard FKs.
- Migration compatibility check: All new migrations are additive.


- Audit date: 2026-04-21
- Auditor: Copilot
- Branch: uwis-michael-merge
- Triggering ticket/task: Application Process search/pagination performance fix
- Total tables reviewed: Whole-schema migration inventory reviewed via `php artisan migrate:status`; targeted relation checks completed for applicant search and application-process query paths

### Whole-Schema Findings
| Area | Status (Pass/Issue) | Notes |
|---|---|---|
| 1NF (atomic values, no repeated groups) | Pass | The change is index-only and does not alter stored row shape or introduce repeated groups. |
| 2NF (full dependency on PK) | Pass | Existing applicant rows and related tables remain unchanged; the added indexes do not affect key dependency rules. |
| 3NF (no transitive dependencies) | Pass | No denormalized data was introduced; the change only improves lookup performance on existing applicant attributes. |
| Lookup normalization | Pass | Added lookup indexes on applicant name fields to keep search queries index-friendly without changing the underlying data model. |
| FK integrity and indexing | Pass | Foreign keys were unchanged; new search indexes support applicant lookup and keep the application-process filter query on indexed paths. |

### Violations or Follow-Ups
| Table | Violation Type | Action | Owner | Target Date |
|---|---|---|---|---|
| None | No new normalization issues introduced | No follow-up required for this performance-only index change | Team | N/A |

### Verification Evidence
- Schema inventory command/output reference: `php artisan migrate:status` run before and after applying `2026_04_21_000001_add_applicant_search_indexes`.
- Applied migration command: `php artisan migrate --path=database/migrations/2026_04_21_000001_add_applicant_search_indexes.php --force`.
- Query-plan validation: `EXPLAIN` on the application-process applicant search now reports `index_merge` instead of a table scan.
- Migration compatibility check: The new migration is additive only and drops only its own two lookup indexes in `down()`.

## Historical Audits
- Keep prior audit blocks below this section in reverse chronological order.
