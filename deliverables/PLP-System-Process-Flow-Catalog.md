# PLP System Process Flow Catalog

Prepared from the implemented Laravel application, registered route surface, controllers/models, portal views, and the existing Registrar business-process and UAT guides.

## Deliverable

- `PLP-System-Process-Flows.vsdx` - editable Microsoft Visio drawing with 37 pages.
- Each page uses consistent actor labels, decision points, and exception/rework notes.
- Page names are numbered so Visio keeps the intended business dependency order.

## Coverage

| Page | Process family | Main feature coverage |
| --- | --- | --- |
| 00 | System overview | Role portals, shared records, permissions, registrar dependencies |
| 01 | Access and login | Module selection, authentication, linkage, authorization, landing |
| 02 | Password lifecycle | First reset, password change, email recovery, registrar reset |
| 03 | Applicant onboarding | Welcome, basic details, four steps, preview, submit, account |
| 04 | Admissions review | Applicant edit, documents, exam/interview, approval, bulk conversion |
| 05 | Applicant tracking | Exam/calendar/result, correspondence, clearance, documents, help |
| 06 | Parent portal | Account creation, student link, grades/profile/calendar, contact/help |
| 07 | Student portal | Schedule, grades, COR, events, offerings, forms, profile, notifications |
| 08 | Faculty operations | Load/download, class list, calendar, messaging, profile, evaluation |
| 09 | Faculty grading | Row entry, grading context, validation, compute, submit |
| 10 | System configuration | Term settings, posting/cutoff, signatories, calendar, announcements, templates |
| 11 | Access control | Accounts, roles, modules, per-user/report access, enforcement, audit identity |
| 12 | Academic master data | Departments, programs, subject/course file, metadata |
| 13 | Curriculum workflow | Curriculum build, four-stage approval, publication, prerequisites, tracking |
| 14 | Academic term lifecycle | First-run/open/publish/close rules and unresolved-work gate |
| 15 | Promotion automation | Readiness, preview, movement generation, academic setup publication |
| 16 | Resource setup | Buildings, hallways, rooms, faculty, departments, allowed subjects |
| 17 | Section scheduling | Curriculum-derived offerings, uniqueness, time assignment, coordination |
| 18 | Assignment/conflicts | Room/teacher eligibility, generation, availability, conflicts, reports |
| 19 | Capacity/merging | Slot monitoring, exception reports, status, section merge validation |
| 20 | Student enrollment | Record/number, dimensions, prerequisites, capacity, account, downstream lists |
| 21 | Student lifecycle | Profile, requirements/files, irregular status, withdraw/reactivate, outputs |
| 22 | Classroom operations | Section/class lists, roster add/export, attendance/import, faculty loads |
| 23 | Grading configuration | Scale, periods, components, transmutation, validity and reuse |
| 24 | Grade approval | Faculty submit, Dean approve, Registrar finalize, reject/rework, post |
| 25 | Grade exceptions | Bulk upload, correction request, override, recompute, audit log |
| 26 | Deficiencies | Incomplete/failing, deficiency records, overdue INC, scholastic comments |
| 27 | Scholarships | Program maintenance, tagging, UniFAST, reporting |
| 28 | Student welfare | Medical, clinic, discipline, family, BED status/days |
| 29 | Communication | Messaging, tickets, stakeholder outreach, help center, notifications |
| 30 | Official documents | TOR, diploma, COR, COG, grade report, dismissal, certificates, layout/issue |
| 31 | Student requests | LOA, cross-enroll, waiver, clearance, F-137A lifecycle |
| 32 | Reports/graduation | Academic/GWA/CWA/guidance reports, certifications, tagging, batch print |
| 33 | Faculty evaluation | Form creation/publication, public token response, results |
| 34 | Alumni tracking | Graduate source, filters, configuration, follow-up view |
| 35 | Data administration | Imports, master files, student update, audit/override logs |
| 36 | End-to-end cycle | Setup -> curriculum -> scheduling -> enrollment -> grading -> close/next term |

## Interpretation notes

- The diagrams represent business processes, not one diagram per HTTP endpoint. CRUD endpoints that operate on the same business object are combined into one maintain/review/validate flow.
- Admissions and Scholarships have implemented routes/controllers but the existing Registrar guide says they may be disabled in the live sidebar. They are included and marked accordingly.
- The normal grade path is Faculty submit -> Dean approve -> Registrar finalize. Grade Override is shown separately because it bypasses that chain and is audit-sensitive.
- The academic dependency order is explicit: master data and a published curriculum precede sections; sections/resources/term precede enrollment; enrollment precedes grades; finalized grades precede official reporting and term close.
- Read-only pages such as calendars, profiles, dashboards, and tracking views are included in the relevant portal/process family rather than duplicated as single-step diagrams.

## Source basis

- `routes/web.php` and the registered `php artisan route:list` surface (550 routes at review time)
- `app/Http/Controllers/**`, related request validators, middleware, models, and support services
- `resources/views/**` portal and registrar view hierarchy
- `docs/manual/PLP-System-User-Manual.md`
- `public/manual/registrar-flow.html`
- `public/manual/registrar-training-flow.html`
- `public/manual/registrar-uat-config-to-grade.html`
- `docs/registrar-page-connections-2026-04-08.md`

## Regeneration

Run `scripts/generate-plp-process-flows.ps1`. It creates the `.vsdx`, reopens it in Microsoft Visio, and verifies page and shape counts before exiting.
