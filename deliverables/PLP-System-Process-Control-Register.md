# PLP System Business Process Control Register

This register is the text companion to the editable Visio process-flow package. It records the purpose, sequential activities, requirements, validation gates, exception handling, outputs, records, and audit evidence for every documented process family.

> Control note: These are business-facing summaries grounded in the implemented application. Exact field rules remain authoritative in the application request validation, database constraints, access middleware, and approved institutional policy.

Process families: **37**

## 00 System Overview - PLP Student Information System - process landscape

**Purpose and scope:** Five role portals share one academic record core. Registrar setup and approvals drive what every other portal can see or do.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | User | Select role module: Applicant, Student, Parent, Faculty, or Registrar | start | - |
| 2 | System | Authenticate account and enforce first-login password setup | process | - |
| 3 | System | Apply role, module, report, and course-scope permissions | decision | Access denied: return to login or show forbidden page. |
| 4 | Role portal | Perform role-specific work or self-service | process | - |
| 5 | Registrar | Maintain academic structure, terms, records, approvals, and official outputs | process | - |
| 6 | System | Persist shared records, notifications, audit events, and document status | process | - |
| 7 | Stakeholders | View approved records, schedules, grades, messages, and documents | end | - |

### Requirements / preconditions

- Active user account linked to one supported portal role
- Published academic term and master data for transactional modules
- Role, module, report, and course-scope permissions configured

### Validation and decision gates

- Authentication, account status, role linkage, and forced-reset gate
- Every protected request is checked against assigned access scope
- Only approved/current records are exposed to downstream portals

### Outputs / records / audit evidence

- Shared student, applicant, faculty, curriculum, enrollment, and grade records
- Portal notifications, support records, and official outputs
- Audit identity, timestamps, status history, and configuration provenance

## 01 Access and Login - Module access and login

**Purpose and scope:** Covers module selection, credential validation, account linkage, access-control middleware, and role landing pages.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | User | Open access page and choose a module | start | - |
| 2 | User | Enter module username and password | process | - |
| 3 | System | Validate credentials and account status | decision | Invalid or inactive: reject sign-in and keep the user on the login page. |
| 4 | System | Verify account is linked to the selected role | decision | Wrong or missing role link: sign out and show account-not-linked message. |
| 5 | System | Check forced password reset and per-user access rules | process | - |
| 6 | System | Route to role landing page | process | - |
| 7 | User | Use only permitted modules and records | end | - |

### Requirements / preconditions

- Correct module selected and valid username or institutional identifier
- Active account with the required student/faculty/registrar/applicant/parent link
- Valid password; first-login users must be able to set a replacement

### Validation and decision gates

- Required credentials, supported module, account status, and password hash
- Authenticated account module must equal the selected portal
- Missing role link or denied module access terminates the session

### Outputs / records / audit evidence

- Regenerated authenticated session and remember-login choice
- Authorized landing page or field-level login error
- Login actor available to access-control and audit middleware

## 02 Password Lifecycle - First login, password change, and recovery

**Purpose and scope:** Covers temporary passwords, mandatory first reset, in-portal changes, registrar-assisted student reset, and email reset.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | User/Admin | Account is created or reset with temporary credentials | start | - |
| 2 | User | Sign in with temporary or existing password | process | - |
| 3 | System | Is first-login reset required? | decision | No: continue directly to the authorized portal. |
| 4 | User | Enter and confirm a compliant new password | process | - |
| 5 | System | Validate, hash, save, and clear reset flag | process | - |
| 6 | User | If forgotten, request email reset token and set a new password | process | - |
| 7 | System | Return user to normal authenticated access | end | - |

### Requirements / preconditions

- Authenticated account or valid, unexpired recovery token
- Current password for an in-portal change when required
- New password and confirmation meeting configured policy

### Validation and decision gates

- Confirmation must match and the password must satisfy length/policy rules
- Recovery token, account identity, throttling, and expiry are checked
- Password is hashed; temporary/reset flag is cleared only after success

### Outputs / records / audit evidence

- Updated password hash and password-reset state
- Recovery token consumed or invalidated
- Success/error notice without exposing credential values

## 03 Applicant Onboarding - Public applicant onboarding and four-step application

**Purpose and scope:** Covers the public apply welcome page, basic details, preview workflow, step saves, submission, and creation of the applicant portal account.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Applicant | Open Apply and accept the onboarding start | start | - |
| 2 | Applicant | Enter basic details | process | - |
| 3 | System | Validate identity/contact data and establish application session | decision | Validation fails: show field errors and retain entered values. |
| 4 | Applicant | Complete Steps 1-4: personal, family, education, and preferences | process | - |
| 5 | Applicant | Review form preview; save progress or reset if needed | process | - |
| 6 | Applicant | Continue and submit application | process | - |
| 7 | System | Create/update applicant record and portal access; show tracking pages | end | - |

### Requirements / preconditions

- Applicant identity/contact details and an active onboarding session
- Personal, family, education, and program-preference information
- Acknowledgement of required declarations before final submission

### Validation and decision gates

- Required fields, formats, enumerated choices, and step ownership
- Duplicate identity/contact/application checks before account creation
- All required steps must be complete before submission is accepted

### Outputs / records / audit evidence

- Draft step data and completion/progress state
- Submitted applicant record plus portal credentials/link
- Validation errors retained by step; submission timestamp/status on success

## 04 Admissions Review - Registrar admissions review and applicant-to-student conversion

**Purpose and scope:** Implemented application, exam, document, approval, lookup, batch, and conversion endpoints; Admissions may be hidden from the live sidebar.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Open applicant queue; filter or bulk-select records | start | - |
| 2 | Registrar | Review/edit four-step form and assigned document checklist | process | - |
| 3 | Registrar | Record exam schedule, exam result, and interview outcome | process | - |
| 4 | Registrar | Are requirements and decisions complete? | decision | No: assign missing requirements, request correction, or keep pending. |
| 5 | Registrar | Set approval status individually or in bulk | process | - |
| 6 | Registrar | Convert approved applicant(s) to student(s) | process | - |
| 7 | System | Generate student record/number and temporary account; retain admission history | end | - |

### Requirements / preconditions

- Submitted applicant with complete identity and program preference
- Assigned document checklist and uploaded/verified requirement evidence
- Exam schedule/result, interview outcome, and authorized reviewer

### Validation and decision gates

- Only eligible submitted applications enter decision processing
- Required documents and admission decisions must be complete for approval
- Conversion blocks duplicates and requires an approved, not-yet-converted applicant

### Outputs / records / audit evidence

- Checklist status, exam/interview data, reviewer decision, and remarks
- Student master record, student number, and temporary linked account
- Admission and conversion status history, including bulk-operation results

## 05 Applicant Tracking - Applicant portal status tracking

**Purpose and scope:** Covers authenticated application editing, exam schedule/result, calendar, correspondence, medical clearance, submitted documents, messaging, help, and notifications.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Applicant | Sign in to applicant portal | start | - |
| 2 | Applicant | Complete or revise application until locked/submitted | process | - |
| 3 | Applicant | Review schedule of exam and academic calendar | process | - |
| 4 | Applicant | Review exam result and correspondence | process | - |
| 5 | Applicant | Check medical clearance and submitted-document status | process | - |
| 6 | Applicant | Read/dismiss notifications or open message/help ticket | process | - |
| 7 | Applicant | Wait for registrar approval or student conversion | end | - |

### Requirements / preconditions

- Authenticated applicant linked to the application being viewed
- Submitted or editable application state appropriate to the requested action
- Registrar-published schedule, result, correspondence, or checklist updates

### Validation and decision gates

- Record ownership prevents viewing another applicant
- Locked/submitted steps cannot be changed outside permitted states
- Only available/published results and documents are displayed

### Outputs / records / audit evidence

- Current application, exam, clearance, and document statuses
- Applicant messages, help tickets, and notification read/dismiss state
- Visible correction requirements and next-action guidance

## 06 Parent Portal - Parent account creation and linked-student access

**Purpose and scope:** Covers student lookup/linking, parent authentication, grades/profile/calendar, contact requests, help, notifications, and password change.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Parent | Open Create Account and search eligible student | start | - |
| 2 | System | Validate student identity and parent-link eligibility | decision | No match or duplicate/invalid link: do not create account. |
| 3 | Parent | Enter relationship and parent account details | process | - |
| 4 | System | Create parent account and parent-student link | process | - |
| 5 | Parent | Sign in and view linked student grades/profile/calendar | process | - |
| 6 | Parent | Send contact request, message, or help ticket; manage notifications/password | process | - |
| 7 | System | Limit all views to linked student records | end | - |

### Requirements / preconditions

- Eligible student identity for lookup and a supported relationship type
- Unique parent account details and verified parent-student linkage data
- Authenticated parent account for all student information views

### Validation and decision gates

- Student match, duplicate link/account, relationship, and required-contact checks
- Every grade/profile/calendar query is restricted to linked students
- Contact/help submissions enforce required fields and request throttles

### Outputs / records / audit evidence

- Parent account and parent-student link
- Read-only linked-student grades, profile, and calendar view
- Contact request, ticket/message, notification, and password-change history

## 07 Student Portal - Student self-service

**Purpose and scope:** Covers schedule, grades, COR, events, section offerings, forms/downloads, profile update, and notifications.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Student | Sign in and complete first-password reset if required | start | - |
| 2 | System | Resolve student identity, current term, and enrollments | process | - |
| 3 | Student | View schedule and available section offerings | process | - |
| 4 | Student | View official grades and Certificate of Registration | process | - |
| 5 | Student | Review academic events and announcements | process | - |
| 6 | Student | Open/download request forms and update allowed profile fields | process | - |
| 7 | Student | Read, mark, or dismiss notifications | end | - |

### Requirements / preconditions

- Authenticated user linked to an active student record
- Resolved academic term and enrollment/section assignments
- Registrar/faculty-published grades, schedule, events, and documents

### Validation and decision gates

- Student can retrieve only their own profile and academic records
- Grades and COR use official/finalized data and current enrollment context
- Profile changes are limited to allowed fields and valid values

### Outputs / records / audit evidence

- Schedule, offerings, grade view, COR, events, and forms
- Permitted student profile updates
- Notification read/dismiss status and access timestamps

## 08 Faculty Operations - Faculty teaching operations

**Purpose and scope:** Covers assigned load, downloadable load, class list, calendar, profile, messaging, evaluation view, and notifications.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Faculty | Sign in and resolve faculty profile | start | - |
| 2 | System | Load assigned subjects, sections, schedules, and term | process | - |
| 3 | Faculty | Review/download faculty load | process | - |
| 4 | Faculty | Open class list for assigned section | process | - |
| 5 | Faculty | Use calendar, messaging, and notifications | process | - |
| 6 | Faculty | Review evaluation information and update allowed profile fields | process | - |
| 7 | Faculty | Proceed to grade encoding for assigned classes | end | - |

### Requirements / preconditions

- Authenticated account linked to an active faculty profile
- Current-term teaching load with section/subject/schedule assignments
- Allowed-subject and course-scope configuration where applicable

### Validation and decision gates

- Only assigned loads and class lists are visible to the faculty member
- Inactive, out-of-term, or unassigned resources are excluded
- Profile edits and communications are limited to authorized fields/audiences

### Outputs / records / audit evidence

- Faculty load, downloadable assignment, roster, and calendar views
- Messages, notifications, profile updates, and evaluation view
- Traceable handoff from assigned class to grade-encoding context

## 09 Faculty Grading - Faculty grade encoding and submission

**Purpose and scope:** Covers grading-sheet selection, row saves, computation inputs, validation, and submission to the approval chain.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Faculty | Open grading sheet for an assigned subject/section | start | - |
| 2 | System | Load enrolled students, components, grading period, and posting window | process | - |
| 3 | Faculty | Encode or update grade rows | process | - |
| 4 | System | Validate values, weights, term, and allowed submission window | decision | Invalid/outside window: reject save or submission and identify the issue. |
| 5 | System | Compute/transmute grades and save draft rows | process | - |
| 6 | Faculty | Submit complete grading sheet | process | - |
| 7 | System | Lock/mark submitted and notify the next approver | end | - |

### Requirements / preconditions

- Assigned faculty/subject/section in an active grading period
- Enrolled student roster plus configured components, weights, and transmutation
- Open posting window and a complete grade sheet for submission

### Validation and decision gates

- Numeric bounds, required components, weight totals, and row ownership
- No grade entry outside the assigned class, term, or posting window
- Submission requires complete valid rows and prevents unauthorized re-editing

### Outputs / records / audit evidence

- Saved draft component/period grades and computed/transmuted result
- Submitted grading status with faculty actor and timestamp
- Validation feedback plus notification to the next approver

## 10 System Configuration - System configuration, calendar, announcements, and templates

**Purpose and scope:** Covers school/semester, posting windows, cutoffs, signatories/files, report details, email sender, curriculum display, calendar, announcements, and document layouts.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar/Admin | Open System Configuration | start | - |
| 2 | Registrar/Admin | Maintain school year/semester and grade-posting/cutoff settings | process | - |
| 3 | Registrar/Admin | Maintain signatory designations, names, signatures, and report details | process | - |
| 4 | Registrar/Admin | Maintain email sender and curriculum display settings | process | - |
| 5 | Registrar/Admin | Create/update calendar events and announcements by audience | process | - |
| 6 | Registrar/Admin | Adjust document-template layout and admission configuration | process | - |
| 7 | System | Publish configuration to dependent scheduling, grading, portals, and documents | end | - |

### Requirements / preconditions

- Authorized registrar/admin and an identified configuration domain
- Valid school/term dates, posting windows, cutoffs, audiences, and signatories
- Approved templates, sender information, and uploaded signature assets when used

### Validation and decision gates

- Required values, date ordering, uniqueness, active-state, and file-type checks
- Configuration changes cannot create conflicting active terms/windows
- Audience and document settings must reference supported modules/templates

### Outputs / records / audit evidence

- Versioned configuration rows, events, announcements, and layouts
- Published settings consumed by scheduling, grading, portals, and reports
- Actor/time audit evidence and safe rejection of invalid changes

## 11 Access Control - User accounts, roles, module access, and report access

**Purpose and scope:** Covers account CRUD, role CRUD, role permissions, per-user overrides, report permissions, status controls, and audit identity.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar/Admin | Create or select a user account | start | - |
| 2 | Registrar/Admin | Assign account type, role, status, and course scope | process | - |
| 3 | Registrar/Admin | Configure role-level module permissions | process | - |
| 4 | Registrar/Admin | Apply per-user module overrides and report access | process | - |
| 5 | System | Validate permission set and save | decision | Invalid or conflicting setup: reject and retain prior access. |
| 6 | System | Enforce access on every protected request | process | - |
| 7 | Audit | Record actor and account changes for review | end | - |

### Requirements / preconditions

- Authorized administrator, target account, and defined role/account type
- Module permission list, report permissions, user overrides, and course scope
- Active/inactive status and valid role relationships

### Validation and decision gates

- Unique accounts/roles and valid permission identifiers
- Role defaults and per-user overrides resolve without invalid scope combinations
- Server-side middleware enforces access even when links are manually requested

### Outputs / records / audit evidence

- Account, role, module, report, and course-scope assignments
- Effective access response or forbidden result
- Audit trail of actor, old/new access, action, and timestamp

## 12 Academic Master Data - Department, program, and subject master data

**Purpose and scope:** Strict dependency chain used by curriculum, rooms, faculty eligibility, sections, scheduling, and enrollment.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Create/maintain faculty department | start | - |
| 2 | Registrar | Create program with unique code and department | process | - |
| 3 | Registrar | Create subject/course-file records with units and lecture/lab hours | process | - |
| 4 | System | Are identifiers and relationships valid? | decision | Duplicate/in-use/invalid: block change or deletion and show validation. |
| 5 | Registrar | Maintain related academic metadata and letter-grade view | process | - |
| 6 | System | Expose active master data to curriculum setup | process | - |
| 7 | Registrar | Begin curriculum construction | end | - |

### Requirements / preconditions

- Authorized registrar and defined department/program hierarchy
- Unique codes/names plus subject units and lecture/laboratory hours
- Valid active-state and parent references

### Validation and decision gates

- Required fields, unique identifiers, numeric unit/hour bounds, and relationships
- Referenced or in-use records cannot be removed when integrity would break
- Only active valid records become selectable in dependent setup

### Outputs / records / audit evidence

- Department, program, subject/course-file, and related lookup records
- Validation/rejection messages for duplicates and protected dependencies
- Master-data provenance consumed by curriculum and scheduling

## 13 Curriculum Workflow - Curriculum build, approval, publication, and prerequisites

**Purpose and scope:** Covers curriculum year, subject matrix, workflow phases, prerequisite/corequisite/equivalent relationships, PDF outputs, and tracking.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Select program and curriculum year; create curriculum | start | - |
| 2 | Registrar | Add subjects by year level and semester; verify units | process | - |
| 3 | Department Head | Review and approve curriculum | process | - |
| 4 | Dean | Review and approve curriculum | process | - |
| 5 | Registrar/Academic Council | Final review and publish | decision | Rejected at any phase: return to editor with corrections; section creation remains blocked. |
| 6 | Registrar | Set prerequisite, corequisite, and equivalent-subject links | process | - |
| 7 | System | Expose published curriculum to tracking, sections, enrollment, and reports | end | - |

### Requirements / preconditions

- Existing program, curriculum year, and approved subject master records
- Year-level/semester subject matrix with units and relationship definitions
- Authorized Department Head, Dean, Registrar, and final publishing authority

### Validation and decision gates

- No duplicate subject placement; valid units, year, semester, and references
- Prerequisite/corequisite/equivalent links cannot be invalid or circular
- Each approval phase must be completed in sequence before publication

### Outputs / records / audit evidence

- Curriculum version, subject matrix, totals, and relationship graph
- Approval/rejection remarks, actor, timestamp, and workflow status
- Published curriculum available to sections, enrollment, tracking, and PDF output

## 14 Academic Term Lifecycle - Open, publish, close, and archive academic terms

**Purpose and scope:** Covers the first-run exception, current-term closure checks, new draft term, publication, and downstream term selection.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Open Academic Term Lifecycle | start | - |
| 2 | System | Is there an active current term? | decision | No active term: allow first-run creation without a prior close. |
| 3 | Registrar | Request close of current term | process | - |
| 4 | System | Check unresolved grading and required close conditions | decision | Not ready: block closure and list unresolved items. |
| 5 | System | Close current term and log transition | process | - |
| 6 | Registrar | Create/open new draft term, review dates, then publish | process | - |
| 7 | System | Make new active term available across modules | end | - |

### Requirements / preconditions

- Authorized registrar and proposed school year/semester dates
- Closure checklist for the current term unless this is first-run setup
- Resolved grading/posting obligations and approved next-term schedule

### Validation and decision gates

- Date ordering, uniqueness, and single-current-term rules
- Term closure is blocked while required grades or close conditions remain unresolved
- Draft must be reviewed before publish; closed terms are protected from normal edits

### Outputs / records / audit evidence

- Draft, active, closed, and archived term states
- Closure readiness exceptions and resolution evidence
- Transition log used by all term-scoped modules

## 15 Promotion Automation - Promotion readiness and academic setup automation

**Purpose and scope:** Covers readiness filters, section drill-down, movement preview, promotion run, generated setup review, and publication.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Select source/target term and program | start | - |
| 2 | System | Calculate student and section promotion readiness | process | - |
| 3 | Registrar | Review counts, section detail, and movement preview | process | - |
| 4 | Registrar | Are exceptions resolved? | decision | No: correct grades/status/curriculum mapping and recalculate. |
| 5 | Registrar | Generate student movement or academic setup | process | - |
| 6 | Registrar | Review generation log and publish | process | - |
| 7 | System | Create next-term structures/movements with traceable history | end | - |

### Requirements / preconditions

- Defined source/target term, program, curriculum, and year-block mapping
- Finalized grades and active student/enrollment status
- Authorized registrar review of readiness and preview results

### Validation and decision gates

- Student is promoted only when academic/status rules are satisfied
- Target mapping, curriculum subjects, and section capacity must exist
- Repeated runs cannot duplicate the same movement/setup records

### Outputs / records / audit evidence

- Readiness counts, exceptions, and section-level drill-down
- Previewed and executed student movements/generated setup
- Run identifier, actor, timestamp, outcome, and publication log

## 16 Resource Setup - Room, building, hallway, faculty, and allowed-subject setup

**Purpose and scope:** Covers physical resource hierarchy, capacity, imports, faculty creation/profile/department, and subject eligibility.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Create building and hallway records | start | - |
| 2 | Registrar | Create rooms with type/capacity and permitted use | process | - |
| 3 | Registrar | Create faculty account and employment profile | process | - |
| 4 | Registrar | Assign department and allowed subjects | process | - |
| 5 | System | Are room/faculty records active and eligible? | decision | No: exclude resource from automatic/manual assignment. |
| 6 | System | Expose eligible resources to scheduling | process | - |
| 7 | Registrar | Proceed to section and schedule assignment | end | - |

### Requirements / preconditions

- Building/hallway hierarchy and room type/capacity details
- Faculty account, employment profile, department, and active status
- Allowed-subject assignments for rooms and faculty

### Validation and decision gates

- Unique resource identifiers, valid capacity, and valid parent references
- Inactive resources are unavailable for new scheduling assignments
- Subject eligibility and room type must be compatible with the scheduled class

### Outputs / records / audit evidence

- Buildings, hallways, rooms, faculty files, and eligibility mappings
- Import/manual-maintenance results and validation errors
- Active candidate pool consumed by scheduling and assignment generation

## 17 Section Scheduling - Section offering and class schedule preparation

**Purpose and scope:** Covers curriculum-derived offerings, duplicate prevention, curriculum subject lookup, manual/automatic time assignment, and coordination review.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Select active term, program, year, and section | start | - |
| 2 | System | Load subjects from the published curriculum | process | - |
| 3 | Registrar | Create section offerings | process | - |
| 4 | System | Is curriculum published and section unique? | decision | No: block creation and require correction/publication. |
| 5 | Registrar | Auto-generate or manually set days/time blocks | process | - |
| 6 | System | Validate schedule completeness and conflicts | process | - |
| 7 | Registrar | Review class schedule and dean/faculty coordination view | end | - |

### Requirements / preconditions

- Active term, program/year/section dimensions, and published curriculum
- Curriculum-derived subject offerings and meeting-hour requirements
- Permitted days/time blocks and authorized scheduler

### Validation and decision gates

- Section/subject offering is unique within the academic context
- Meeting pattern satisfies required hours and valid time boundaries
- Schedule collision and completeness checks pass before release

### Outputs / records / audit evidence

- Section offerings and class schedule rows
- Conflict/completeness findings and coordinator review result
- Published schedule used by room/faculty assignment and enrollment

## 18 Assignment and Conflicts - Room and teacher generation, assignment, and conflict handling

**Purpose and scope:** Covers allowed-subject generation, pending availability, automatic/manual assignment, schedule collision checks, and assignment reports.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Select term and unassigned section-subject rows | start | - |
| 2 | System | Generate eligible room and teacher candidates | process | - |
| 3 | System | Check capacity, room type, allowed subject, and availability | process | - |
| 4 | System | Is a conflict-free candidate available? | decision | No: leave pending, adjust schedule/resource rules, and regenerate. |
| 5 | Registrar | Confirm automatic or manual assignment | process | - |
| 6 | System | Save room/faculty assignment without double booking | process | - |
| 7 | Registrar | Review room and teacher assignment reports | end | - |

### Requirements / preconditions

- Scheduled section-subject rows awaiting resources
- Active eligible room/faculty pools with subject, capacity, and availability data
- Automatic generation rules or an authorized manual assignment

### Validation and decision gates

- No overlapping room or faculty booking in the same term/time
- Room capacity/type and allowed-subject constraints must pass
- Manual selection is rejected when the candidate is inactive or ineligible

### Outputs / records / audit evidence

- Confirmed room and faculty assignments
- Pending/unassigned rows with explicit conflict reasons
- Assignment reports and actor/time change evidence

## 19 Capacity and Merging - Slot monitoring, section status, and section merging

**Purpose and scope:** Covers actual size/capacity, under-20/dissolved/closed reports, edits, compatible merge selection, and consolidated results.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Open slot monitoring for a term | start | - |
| 2 | System | Compare capacity, enrollment count, and section status | process | - |
| 3 | Registrar | Adjust capacity/status and review exception reports | process | - |
| 4 | Registrar | Select compatible under-enrolled sections for merge | process | - |
| 5 | System | Do subject, term, schedule, and target capacity permit merge? | decision | No: reject merge and identify the incompatibility. |
| 6 | System | Move enrollments and preserve a consistent target schedule | process | - |
| 7 | Registrar | Verify merged counts, status, and audit result | end | - |

### Requirements / preconditions

- Current section capacities, enrollment counts, schedules, and statuses
- Compatible source and target sections in the same academic context
- Authorized registrar review of the proposed merge

### Validation and decision gates

- Actual enrollment cannot exceed approved target capacity
- Merge requires compatible term, subject, schedule, and section status
- Student enrollment uniqueness is preserved during movement

### Outputs / records / audit evidence

- Slot-monitoring totals and under-capacity/closed/dissolved exception lists
- Merge operation, moved enrollments, and resulting section status
- Pre/post counts, rejection reasons, actor, and timestamp

## 20 Student Enrollment - Student creation, enrollment, and account provisioning

**Purpose and scope:** Covers direct student entry or converted applicant, course scope, curriculum/year/section, prerequisites, capacity, and temporary login.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Search existing student or create new student | start | - |
| 2 | System | Generate/validate student number and identity | process | - |
| 3 | Registrar | Assign program, curriculum year, year level, and term | process | - |
| 4 | Registrar | Select section and eligible subjects | process | - |
| 5 | System | Are prerequisites, term, capacity, and duplicate rules satisfied? | decision | No: block enrollment and show the unmet rule. |
| 6 | System | Save enrollments and create temporary student account if new | process | - |
| 7 | System | Update enrollment, section/class lists, faculty view, COR, and portal | end | - |

### Requirements / preconditions

- Active student record/number and selected academic term
- Published curriculum, offered section subjects, capacity, and student dimensions
- Resolved prerequisites/equivalencies and permitted enrollment status

### Validation and decision gates

- No duplicate student-term-subject enrollment
- Prerequisite, program/year/block, capacity, and active-term checks
- Withdrawal/inactive status or missing setup blocks enrollment

### Outputs / records / audit evidence

- Student enrollment and class-list membership
- Temporary/linked portal account when provisioning is required
- Enrollment decision, exceptions, actor/time, COR and grading eligibility

## 21 Student Record Lifecycle - Student profile, status, requirements, withdrawal, and reactivation

**Purpose and scope:** Covers profile maintenance, requirement files, irregular recalculation, scholastic comments, status transitions, and printable academic records.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Search and open student record | start | - |
| 2 | Registrar | Update profile, academic dimensions, background, or requirements/files | process | - |
| 3 | System | Validate course scope and referenced master data | process | - |
| 4 | Registrar | Add scholastic comments or recalculate irregular status | process | - |
| 5 | Registrar | Withdraw, reactivate, or change student status | decision | Invalid transition or protected record: reject and retain current status. |
| 6 | System | Propagate status to enrollment and downstream eligibility | process | - |
| 7 | Registrar | View/print TOR, diploma, report of grades, or academic record | end | - |

### Requirements / preconditions

- Unique student number and complete core identity/academic dimensions
- Program, curriculum, year/block, status, and requirement definitions
- Authorized supporting files for profile, requirement, or status changes

### Validation and decision gates

- Required fields, formats, uniqueness, file rules, and lookup references
- Status transitions protect active enrollments and academic-history integrity
- Only authorized fields/records can be updated or downloaded

### Outputs / records / audit evidence

- Student profile, image, requirements, files, status, and movement history
- Irregular/withdrawn/reactivated state and related reasons
- Updated class lists, reports, account linkage, and audit trail

## 22 Classroom Operations - Section list, class list, attendance, and faculty loads

**Purpose and scope:** Covers section filtering, roster search/add/export, attendance entry/import, faculty assignment, allowed subjects, and strength-of-classes output.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar/Faculty | Select active term, section, or assigned subject | start | - |
| 2 | System | Load enrolled roster and faculty assignment | process | - |
| 3 | Registrar | Review section/class list; search, add allowed student, or export | process | - |
| 4 | Registrar/Faculty | Record or import attendance for a class session | process | - |
| 5 | System | Validate student enrollment and attendance values | decision | Invalid row: report error and do not silently import it. |
| 6 | Registrar | Review/update faculty load and allowed subjects | process | - |
| 7 | System | Update rosters, attendance records, loads, and printable class-strength output | end | - |

### Requirements / preconditions

- Active term, section offering, assigned faculty, and enrolled roster
- Authorized registrar/faculty scope for the selected class
- Valid attendance/import file when bulk attendance is used

### Validation and decision gates

- Roster changes require a valid student enrollment and avoid duplicates
- Attendance rows must match the selected class/date/student
- Exports and load reports respect term, section, and access filters

### Outputs / records / audit evidence

- Class list, attendance, faculty load, and printable/exportable outputs
- Import validation report and applied row counts
- Roster/attendance changes with actor and timestamp

## 23 Grading Configuration - Grading rules, periods, components, and transmutation

**Purpose and scope:** Covers scale CRUD, posting periods, component weights, universal/subject tables, copying, and downstream computation.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Define official grading scale/rules | start | - |
| 2 | Registrar | Define grading periods and posting windows | process | - |
| 3 | Registrar | Define component weights by subject/context | process | - |
| 4 | System | Do component weights and grade bands form a valid configuration? | decision | No: reject configuration and identify gaps/overlaps or invalid total. |
| 5 | Registrar | Create/copy/update transmutation bands | process | - |
| 6 | System | Activate configuration for grade entry and calculation | process | - |
| 7 | System | Use the same rules for grades, GWA/CWA, reports, and certificates | end | - |

### Requirements / preconditions

- Defined grading scale, periods, components, and grade rules
- Component names/weights and transmutation bands appropriate to the program/term
- Authorized registrar/admin and intended effective scope

### Validation and decision gates

- Component weights and required totals must be valid
- Ranges/bands cannot overlap, leave invalid gaps, or reverse boundaries
- Referenced configurations are protected from unsafe deletion or scope changes

### Outputs / records / audit evidence

- Reusable grading rules, periods, components, and transmutation tables
- Effective scope/version and validation errors
- Configuration consumed consistently by faculty entry and recomputation

## 24 Grade Approval - Faculty-to-Dean-to-Registrar grade approval

**Purpose and scope:** Normal grade lifecycle: submit, dean review, registrar finalization, rejection/rework, posting, and portal visibility.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Faculty | Submit completed grading sheet | start | - |
| 2 | Dean | Review submitted class and approve or reject | decision | Reject: return to Faculty with reason; Faculty corrects and resubmits. |
| 3 | System | Mark dean-approved; queue for registrar | process | - |
| 4 | Registrar | Review approved sheet and finalize or reject | decision | Reject: return through correction path with reason. |
| 5 | System | Post final subject grades to official records | process | - |
| 6 | System | Refresh deficiencies, academic records, GWA/CWA, and reports | process | - |
| 7 | Student/Parent | View official posted grades according to access rules | end | - |

### Requirements / preconditions

- Complete faculty-submitted grade sheet
- Assigned Dean and Registrar approval authority
- Open approval/finalization state for the correct term and class

### Validation and decision gates

- Only the current authorized stage owner can approve/reject
- Incomplete/invalid/unsubmitted sheets cannot advance
- Finalization is idempotent and locks the official result from normal edits

### Outputs / records / audit evidence

- Submitted, dean-approved, registrar-finalized, or rejected status
- Reviewer remarks, actor, timestamps, and notification events
- Official posted grades available to students, reports, and closure checks

## 25 Grade Exceptions - Bulk grade import, correction request, and grade override

**Purpose and scope:** Covers template download/import validation, submission, academic-record correction approval/rejection, exceptional override, and immutable audit evidence.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar/Faculty | Choose template/import, correction request, or override path | start | - |
| 2 | System | Load exact term, subject, student, and current grade context | process | - |
| 3 | User | Enter/import proposed grade and reason/evidence | process | - |
| 4 | System | Validate row mapping, grade range, authorization, and current status | decision | Invalid: reject atomically or report row-level errors; preserve current official grade. |
| 5 | Registrar | Approve correction or explicitly confirm exceptional override | process | - |
| 6 | System | Update official record and recompute dependent results | process | - |
| 7 | Audit | Record before/after value, reason, actor, time, and outcome | end | - |

### Requirements / preconditions

- Identified student/class/term and an authorized correction or override request
- Corrected grade data, reason, supporting evidence, and approver authority
- Valid upload template when applying a controlled bulk correction

### Validation and decision gates

- Identity, numeric bounds, class scope, and duplicate-row checks
- Override/correction requires reason and permission appropriate to finalized data
- Recomputation uses the active grading/transmutation rules and preserves history

### Outputs / records / audit evidence

- Before/after grade values, request status, decision, and remarks
- Bulk-upload error report or successful applied-row count
- Immutable override/audit log with actor, time, source, and reason

## 26 Deficiencies - Incomplete/failing grades, deficiencies, and scholastic comments

**Purpose and scope:** Covers automatic views from posted grades, manual deficiency records, INC aging process, updates/removals, and record comments.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | System | Read finalized grades and configured passing/INC rules | start | - |
| 2 | System | Identify incomplete or failing outcomes | process | - |
| 3 | Registrar | Search student and review computed deficiency history | process | - |
| 4 | Registrar | Add/update/remove supported manual deficiency detail | process | - |
| 5 | Registrar/Admin | Run overdue INC process when authorized | process | - |
| 6 | Registrar | Add scholastic comment to the student record | process | - |
| 7 | System | Expose current deficiency state to records and reports | end | - |

### Requirements / preconditions

- Enrolled student and an incomplete/failing or otherwise deficient grade condition
- Defined deficiency type, due date, required action, and responsible owner
- Configured overdue-INC rules for automated processing

### Validation and decision gates

- Deficiency must reference a valid student, subject, term, and grade record
- Resolution requires authorized evidence and a valid replacement/status outcome
- Overdue automation selects only eligible unresolved records and avoids duplicates

### Outputs / records / audit evidence

- Deficiency, scholastic comment, due date, status, and resolution
- Overdue-processing run result and exception list
- Updated academic status/reporting plus actor/time history

## 27 Scholarships - Scholarship programs, student tagging, UniFAST, and reports

**Purpose and scope:** Implemented scholarship CRUD, per-student tags, UniFAST toggle, and scholars-by-type report; module may be hidden from the live sidebar.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Create/maintain scholarship program and type | start | - |
| 2 | Registrar | Search and open eligible student record | process | - |
| 3 | Registrar | Tag student to scholarship and set relevant term/status | process | - |
| 4 | System | Is the tag unique and student eligible? | decision | No: reject duplicate/invalid tag and preserve existing record. |
| 5 | Registrar | Toggle UniFAST classification when applicable | process | - |
| 6 | Registrar | Remove/close tag when entitlement ends | process | - |
| 7 | System | Produce scholars-by-type and UniFAST-related reports | end | - |

### Requirements / preconditions

- Defined active scholarship program and eligible student record
- Coverage/eligibility information and supporting scholarship details
- Authorized registrar selection or UniFAST/report criteria

### Validation and decision gates

- Program and student must be active and within applicable term/scope
- Duplicate tagging for the same program/period is prevented
- Required scholarship fields and report filters must be valid

### Outputs / records / audit evidence

- Scholarship program and student-program tagging
- Eligibility/coverage status and UniFAST/report output
- Maintenance and tagging actor/time evidence

## 28 Student Welfare - Medical, clinic, discipline, family, and BED maintenance

**Purpose and scope:** Covers medical details, clinic visits, disciplinary subjects/cases/actions, family view, BED statuses/days, and scoped record maintenance.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Search and open student welfare/account record | start | - |
| 2 | Registrar | Maintain medical information or clinic visit record | process | - |
| 3 | Registrar | Create/select discipline student and program context | process | - |
| 4 | Registrar | Add case type, action, dates, narrative, and status | process | - |
| 5 | System | Are required references, dates, and course scope valid? | decision | No: reject change and keep previous record. |
| 6 | Registrar/Admin | Review family information; maintain BED status/day lookups | process | - |
| 7 | System | Store traceable welfare records and expose authorized views only | end | - |

### Requirements / preconditions

- Valid student identity and authorized welfare/registrar user
- Medical, clinic, discipline, family, or BED status details relevant to the case
- Case/action types and supporting dates/remarks where required

### Validation and decision gates

- Required dates/types, valid student references, and lookup values
- Sensitive welfare records are restricted by role and student scope
- Discipline/welfare history is preserved when a current status is updated

### Outputs / records / audit evidence

- Medical/clinic entries, discipline cases/actions, and family details
- BED student status/day records and current case state
- Restricted audit trail containing actor, timestamps, and disposition

## 29 Communication - Messaging, ticketing, stakeholder outreach, help, and notifications

**Purpose and scope:** Shared communication pattern across Registrar, Applicant, Parent, Faculty, and Student portals.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | User/Registrar | Compose message, contact request, ticket, or stakeholder notice | start | - |
| 2 | System | Validate sender, recipient/audience, topic, and rate limits | process | - |
| 3 | System | Is submission complete and authorized? | decision | No: reject and show validation/access feedback. |
| 4 | System | Save message/ticket and create delivery records | process | - |
| 5 | Recipient | Receive portal notification or configured email | process | - |
| 6 | Registrar/User | Review, reply/update status, mark read, or dismiss | process | - |
| 7 | System | Retain communication/status history for follow-up | end | - |

### Requirements / preconditions

- Authenticated sender, permitted recipient/audience, and message purpose
- Required subject/body/topic/channel and valid referenced ticket when replying
- Configured notification/email template and sender when external delivery is used

### Validation and decision gates

- Required content, recipient eligibility, throttling, and ticket ownership
- Audience filters prevent delivery to unintended portal roles
- Status transitions follow open/in-progress/resolved rules

### Outputs / records / audit evidence

- Registrar messages, stakeholder outreach, support tickets, and replies
- Notification delivery/read/dismiss state and email-template version
- Sender/handler, timestamps, ticket status, and delivery outcome

## 30 Official Documents - Official document and certificate issuance

**Purpose and scope:** Covers student selection, eligibility/data assembly, template/signatory layout, preview, issue/print, issuance history, and bulk issuance where implemented.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Choose document: TOR, diploma, COR, COG, grade report, dismissal, or certificate | start | - |
| 2 | Registrar | Search/select student(s) and term/context | process | - |
| 3 | System | Assemble identity, enrollment, grades, honors, and signatory/template data | process | - |
| 4 | System | Is the student eligible and required data complete? | decision | No: block issue or show missing requirements/data. |
| 5 | Registrar | Preview and, where allowed, adjust reusable layout/signatories | process | - |
| 6 | Registrar | Issue, tag, export, bulk issue, or print document | process | - |
| 7 | System | Record issuance and render the official output | end | - |

### Requirements / preconditions

- Correct student identity and requested document type
- Finalized/authorized academic data, signatories, report details, and layout
- Certificate/request metadata and printing/batch criteria

### Validation and decision gates

- Document-specific completeness and eligibility checks
- Only finalized grades/enrollments are treated as official
- Unique certificate/issue controls plus valid template/signatory assets

### Outputs / records / audit evidence

- Rendered TOR, diploma, COR, COG, grade report, dismissal, or certificate
- Issue/register status, reference number, batch result, and print/download output
- Source-data snapshot, signatory/template version, actor, and issue timestamp

## 31 Student Requests - Leave, cross-enrollment, waiver/cancellation, clearance, and F-137A requests

**Purpose and scope:** Covers enrolled/non-enrolled/late LOA forms, cross-enroll and waiver records, graduation/other clearance, and F-137A search/request/print.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Student/Registrar | Select request/form type and student | start | - |
| 2 | System | Load student status, term, enrollment, and prior request context | process | - |
| 3 | Registrar | Enter reason, dates, destination/details, and supporting information | process | - |
| 4 | System | Does request satisfy type-specific eligibility and required fields? | decision | No: retain draft or reject until requirements are corrected. |
| 5 | Registrar | Create/update request and assign status/approval result | process | - |
| 6 | Registrar | Generate printable form, clearance, or F-137A request | process | - |
| 7 | System | Retain request lifecycle for reports and student record | end | - |

### Requirements / preconditions

- Linked student/applicant request with selected request type and reason
- Required supporting form/documents, dates, and academic context
- Assigned registrar reviewer and any downstream clearance/monitoring owners

### Validation and decision gates

- Eligibility, required fields/files, duplicate active request, and date rules
- Transitions follow submitted-review-approved/rejected/completed order
- Approval cannot complete while required clearance/evidence is outstanding

### Outputs / records / audit evidence

- LOA, cross-enroll, waiver/cancellation, clearance, or F-137A request
- Reviewer decision, remarks, status, and monitoring milestones
- Notifications plus complete requester/reviewer timestamp history

## 32 Reports and Graduation - Reports, certifications, graduate tagging, and batch print

**Purpose and scope:** Covers academic/GWA/CWA/guidance/LOA/waiver reports, certifications, UniFAST/OSS-NSTP outputs, graduate tagging, and batch rendering.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Choose report/certification and permitted scope | start | - |
| 2 | Registrar | Set term, program, section, student, status, and output filters | process | - |
| 3 | System | Enforce report access and gather official source records | process | - |
| 4 | System | Are filters, permissions, and source data valid? | decision | No: block output and identify missing access/data. |
| 5 | Registrar | Review results; tag graduate or issue certification if applicable | process | - |
| 6 | Registrar | Export/print one record or batch by section/students | process | - |
| 7 | System | Record issuance/tagging and render authoritative output | end | - |

### Requirements / preconditions

- Authorized report access and defined term/program/student filters
- Finalized source grades, enrollments, deficiencies, and student status
- Graduation criteria, certification type, and batch-print parameters

### Validation and decision gates

- Report permission, filter validity, and scoped source-query checks
- Graduate tagging requires completion/readiness and avoids duplicate active tags
- Batch/certification output is blocked when required official data is missing

### Outputs / records / audit evidence

- Academic, GWA, CWA, guidance, LOA, waiver, monitoring, and certification reports
- Graduate tagging and certificate issuance records
- Export/print parameters, source status, actor, and generation timestamp

## 33 Faculty Evaluation - Faculty evaluation form publication and response

**Purpose and scope:** Covers registrar form definition/publication, tokenized public response, rate limiting, response persistence, and faculty evaluation view.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Create evaluation form and questions | start | - |
| 2 | Registrar | Review and publish evaluation form | process | - |
| 3 | System | Generate active token/link and evaluation window | process | - |
| 4 | Evaluator | Open tokenized public evaluation and submit response | process | - |
| 5 | System | Is token valid, form active, and response valid? | decision | No: reject expired/invalid/duplicate or malformed submission. |
| 6 | System | Store response and aggregate evaluation information | process | - |
| 7 | Faculty/Registrar | View authorized evaluation results | end | - |

### Requirements / preconditions

- Evaluation form with questions/options and intended faculty/period scope
- Published form and valid public/internal response token where applicable
- Evaluator responses and any required acknowledgement

### Validation and decision gates

- Required questions/options, active publication window, and valid token
- Duplicate/unauthorized submissions are rejected according to response rules
- Result access is restricted to authorized faculty/registrar scope

### Outputs / records / audit evidence

- Form version, questions, publication state, and target scope
- Evaluation responses and aggregate/result view
- Submission/result timestamps with privacy-preserving evaluator handling

## 34 Alumni Tracking - Alumni tracker and graduate follow-up

**Purpose and scope:** Covers graduate identification, tracker filtering, configuration, graduation details, and downstream alumni reporting/contact readiness.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar | Open alumni tracker and load configured term/status rules | start | - |
| 2 | System | Identify graduated/tagged student records | process | - |
| 3 | Registrar | Search/filter alumni and review graduation details | process | - |
| 4 | Registrar | Adjust tracker configuration when authorized | process | - |
| 5 | System | Is the student correctly tagged as graduate? | decision | No: return to graduate tagging/academic records before inclusion. |
| 6 | System | Persist tracker configuration and current alumni view | process | - |
| 7 | Registrar | Use alumni list for authorized follow-up/reporting | end | - |

### Requirements / preconditions

- Correctly graduated/tagged student records and configured alumni criteria
- Authorized registrar with program/term/status filters
- Approved follow-up/reporting purpose and permitted contact scope

### Validation and decision gates

- Only valid graduate records enter the alumni population
- Filters and configuration reference supported terms/statuses
- Access and export remain within authorized alumni/report scope

### Outputs / records / audit evidence

- Alumni list, graduation details, filters, and tracker configuration
- Excluded/not-yet-tagged exceptions returned to graduation processing
- Configuration and authorized follow-up/report generation history

## 35 Data Administration - Data imports, master files, student update, grade override logs, and audit trail

**Purpose and scope:** Covers template-based bulk loads, row validation, master-file maintenance, controlled update runs, and audit review.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar/Admin | Choose import, master-file maintenance, or update run | start | - |
| 2 | Registrar/Admin | Download template or load current master records | process | - |
| 3 | Registrar/Admin | Prepare rooms, assignments, schedules, offerings, subjects, curriculum, faculty, profile, or grade data | process | - |
| 4 | System | Validate schema, references, duplicates, scope, and each row | decision | Errors: report exact rows and do not silently apply invalid data. |
| 5 | Registrar/Admin | Confirm clean import/update or save controlled master change | process | - |
| 6 | System | Apply transaction, refresh dependent views, and record outcome | process | - |
| 7 | Audit | Review audit trail and grade-override logs by actor/action/time | end | - |

### Requirements / preconditions

- Authorized registrar/admin and the official template/schema
- Complete source file with stable identifiers and referenced master data
- Backup/review readiness for high-impact imports or controlled update runs

### Validation and decision gates

- File type, headers, row schema, duplicates, references, scope, and value bounds
- Invalid rows are reported explicitly and are not silently committed
- Transactional apply prevents partial inconsistent data and repeated-run duplication

### Outputs / records / audit evidence

- Import preview/errors, accepted/rejected counts, and applied records
- Master-file or student-update run result and dependent refresh status
- Source filename/hash where available, actor, time, action, and override/audit logs

## 36 End to End Cycle - End-to-end academic operating cycle

**Purpose and scope:** Dependency order verified by the system guides: setup before scheduling, scheduling before enrollment, enrollment before grading, and grading before official outputs/term close.

### Process activities

| # | Actor / owner | Activity | Control type | Exception / rework |
| ---: | --- | --- | --- | --- |
| 1 | Registrar/Admin | Configure system, access, calendar, and grading rules | start | - |
| 2 | Registrar | Create departments, programs, subjects, and publish curriculum | process | - |
| 3 | Registrar | Create resources; open term; build sections, schedules, rooms, and faculty assignments | process | - |
| 4 | Registrar | Review admissions; create/enroll students and provision accounts | process | - |
| 5 | Faculty/Dean/Registrar | Record attendance and move grades through submit, approve, finalize | process | - |
| 6 | Registrar | Resolve deficiencies; issue forms, certificates, and reports; tag graduates | process | - |
| 7 | Registrar/System | Validate closure, close term, promote/generate next setup, and repeat | end | - |

### Requirements / preconditions

- Approved governance calendar, owners, configuration, and access
- Complete master data, published curriculum, resources, term, and grading rules
- Operational readiness at every handoff from admission through term closure

### Validation and decision gates

- Dependency gates enforce setup before scheduling, then enrollment, then grading
- Approval/finalization and unresolved-exception checks precede official outputs
- Term closure and promotion run only after readiness controls pass

### Outputs / records / audit evidence

- Traceable academic-cycle state across every module
- Exception backlog, approval evidence, official documents, and reports
- Closed-term audit trail plus promoted/generated next-term setup

