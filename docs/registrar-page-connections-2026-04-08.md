# Registrar Page Connection Map (2026-04-08)

## Scope
This map traces Registrar Academic Master page connections from route to controller to Blade and page script.

## Route Group Root
- Prefix: `/registrar/registrar-menu/academic-master`
- Middleware chain inherited from registrar group: `auth`, `force_password_reset`
- Source: `routes/web.php`

## Registrar-Wide Route Group Map

| Route Group Prefix | Main Controller Scope |
|---|---|
| `/registrar/process/*` | `RegistrarController` process workflows |
| `/registrar/registrar-menu/*` | `RegistrarController` registrar menu pages |
| `/registrar/services/classroom-faculty/*` | `Registrar\Services\ClassListController`, `AttendanceController`, `FacultyLoadsController` |
| `/registrar/services/grading-academic/*` | `Registrar\Services\GradingAcademicController` |
| `/registrar/services/reports-admin/*` | `Registrar\Services\ReportsAdminController` |
| `/registrar/services/student-account/*` | `Registrar\Services\StudentAccountController` |
| `/registrar/admin-tools/*` | `Registrar\Services\AdminToolsController` |

Use these route-group anchors in `routes/web.php` to trace any Registrar page end-to-end before editing:
- `Route::prefix('process')`
- `Route::prefix('registrar-menu')`
- `Route::prefix('services')`
- `Route::prefix('admin-tools')`

## Academic Master Pages

| URL | Route Name | Controller Method | Blade View |
|---|---|---|---|
| `/program-file` | `registrar.registrar-menu.academic-master.program-file` | `RegistrarController@programFile` | `registrar.registrar-menu.academic-master.program-file` |
| `/subject-file` | `registrar.registrar-menu.academic-master.subject-file` | `RegistrarController@subjectFile` | `registrar.registrar-menu.academic-master.subject-file` |
| `/curriculum-file` | `registrar.registrar-menu.academic-master.curriculum-file` | `RegistrarController@curriculumFile` | `registrar.registrar-menu.academic-master.curriculum-file` |
| `/pre-requisites` | `registrar.registrar-menu.academic-master.pre-requisites` | `RegistrarController@preRequisites` | `registrar.registrar-menu.academic-master.pre-requisites` |

## Subject File Data/API Connections

### HTTP Endpoints
- `GET /subject-file/data` -> `RegistrarController@subjectFileData`
- `POST /subject-file` -> `RegistrarController@storeSubjectFile`
- `PUT /subject-file/{subjectId}` -> `RegistrarController@updateSubjectFile`
- `DELETE /subject-file/{subjectId}` -> `RegistrarController@destroySubjectFile`

### UI Binding Chain
1. Blade page `resources/views/registrar/registrar-menu/academic-master/subject-file.blade.php` injects endpoint URLs into page `data-*` attributes.
2. Script `public/js/subject-file.js` reads these attributes (`SF_FETCH_URL`, `SF_STORE_URL`, `SF_UPDATE_URL_TEMPLATE`, `SF_DELETE_URL_TEMPLATE`).
3. Script calls `loadSubjects(search, page)` to fetch paginated records.
4. Controller returns `rows` + `meta` payload from `subjectFileData`.
5. Script renders table rows and pagination controls (`sfPerPage`, `sfPrevBtn`, `sfNextBtn`).

## Subject File Data Storage Chain
- Model: `app/Subject.php`
- Table: `subjects`
- Subject-file marker: `is_subject_file_record = 1`
- Subject-file booleans: `is_core`, `is_applied`, `is_specialized`
- Column migration: `database/migrations/2026_04_08_000250_ensure_subject_file_columns_on_subjects_table.php`
- Performance index migration: `database/migrations/2026_04_08_000260_add_subject_file_indexes_to_subjects_table.php`

## Seeder and Data Volume Chain
- Factory: `database/factories/SubjectFactory.php`
- High-volume seeder: `database/seeds/HighVolumeSubjectFileSeeder.php`
- Seeder entrypoint: `database/seeds/DatabaseSeeder.php` (via `SEED_HIGH_VOLUME_TRASH=true`)

## Notes for Tracing Other Registrar Pages
Use this same pattern for each page:
1. Route declaration in `routes/web.php`
2. Controller method in `app/Http/Controllers/Registrar/RegistrarController.php` (or `app/Http/Controllers/Registrar/Services/*Controller.php`)
3. `return view(...)` target in method
4. View-specific script include and API endpoint bindings in Blade
