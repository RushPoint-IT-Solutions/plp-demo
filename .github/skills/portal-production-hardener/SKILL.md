---
name: "portal-production-hardener"
description: "Complete self‑healing production hardener for Laravel 5.7 / PHP 7.4. Single‑skill full lifecycle: load memory → UI scan → component reuse → 3NF normalization → security hardening → seeding → API audit → auth bypass → input validation → Playwright debug/chaos → performance → preflight → persistence/skill evolution. Iterates until all verifications pass. No external skill dependencies."
---

# Portal Production Hardener (All‑in‑One, Self‑Healing, Self‑Improving, Component‑Aware) – Laravel 5.7

## Purpose
Transform a development Laravel 5.7 project into a production‑ready, secure, and scalable school portal. **All phases are contained in this single skill.** The workflow begins by loading any previously saved project memory, then scans the UI, identifies reusable components to prevent duplication, and proceeds through all hardening and testing phases. A self‑healing loop iterates until all Playwright and MCP verifications pass. Finally, a project memory summary is created and optionally embedded into the skill for future runs.

## Prerequisites
- Laravel 5.7 project with database connection configured.
- Playwright MCP server accessible.
- MySQL/PostgreSQL MCP accessible.
- `.ultimate-architect.json` configuration file at project root.

### Configuration File (`.ultimate-architect.json`)
```json
{
  "locale": "en_PH",
  "seeder": {
    "applicants": 2000,
    "students": 1000,
    "faculty": 100
  },
  "normalization": {
    "destructive": true,
    "generate_rollback": true
  },
  "chaos_testing": {
    "enabled": true,
    "target_url": "http://localhost:8000"
  },
  "version_lock": {
    "laravel": "5.7.29",
    "php": "7.4.30"
  },
  "self_healing": {
    "max_iterations": 5,
    "auto_fix": true,
    "halt_on_critical": true
  }
}
```

---

# PHASE 0: Load Project Memory (Context Initialization)

## Purpose
Before any scanning or modification, load previously saved project memory to accelerate execution and preserve customizations.

## Steps

1. **Check for Appended Memory in Skill File**
   - Read the current skill file (the file containing this definition).
   - Search for the section `## Project‑Specific Memory (Auto‑Appended)`.
   - If found, extract the content after that heading. Parse markdown to identify:
     - Previously created tables and relationships.
     - Custom validation rules.
     - Special business logic.
     - Unresolved issues from last run.
     - Playwright/Chaos test summary.
     - Known reusable components.
   - Store in runtime context object.

2. **Fallback: Check `.security-audits/PROJECT_MEMORY.md`**
   - If no appended memory in skill file, check `.security-audits/PROJECT_MEMORY.md` and load similarly.

3. **Apply Context**
   - Use known tables/fields to potentially skip redundant UI scanning.
   - Prioritize fixing unresolved issues.
   - Note any existing reusable components to avoid recreating them.

4. **Log**
   - Save `.security-audits/context-loaded.txt` indicating whether memory was found.

---

# PHASE 1: UI Data Mapping (Context‑Aware)

## Purpose
Scan web pages to identify data requirements. Use existing contract if available and page hash matches; otherwise fresh scan.

## Tools
- Playwright MCP: `browser_navigate`, `browser_snapshot`, `browser_evaluate`
- Filesystem

## Steps

1. **Check Existing UI Contract**
   - If `.security-audits/ui-data-contract.json` exists and memory context indicates no structural changes, load and skip to Phase 1.5.
   - Else, proceed with fresh scan.

2. **Navigate to Key Pages**
   - Use `browser_navigate` to visit:
     - Applicant Form page
     - Student Profile page
     - Registrar Dashboard page
     - Any other major CRUD pages (from memory context).
   - Wait for network idle.

3. **Extract Form Fields and Table Headers**
   - Use `browser_evaluate` to collect:
     - All `<input>` names, types, required.
     - All `<select>` names and options.
     - All `<textarea>` names.
     - All `<table>` header texts (`<th>`).
   - Example script:
     ```javascript
     () => {
       const inputs = Array.from(document.querySelectorAll('input, select, textarea')).map(el => ({
         name: el.name,
         type: el.type || el.tagName.toLowerCase(),
         required: el.required
       }));
       const headers = Array.from(document.querySelectorAll('th')).map(th => th.innerText.trim());
       return { inputs, headers };
     }
     ```

4. **Generate UI Data Contract**
   - Save to `.security-audits/ui-data-contract.json`.

---

# PHASE 1.5: Component Reusability & Consistency Enforcement

## Purpose
Analyze the UI structure across scanned pages to identify repeating patterns (pagination, tables, modals, filter bars, cards, buttons) and enforce the use of shared Blade components or partials. This prevents duplicate code and ensures consistent behavior and styling.

## Tools
- Filesystem (`read_file`, `write_file`, `search_files`)
- Playwright MCP (to capture additional UI structure if needed)

## Steps

### Step 1: Identify Common UI Patterns
- From the UI scan in Phase 1, analyze the DOM snapshots and extracted elements to detect:
  - **Pagination:** Presence of `<nav>` with `pagination` class or similar.
  - **Data Tables:** Repeated `<table>` structures with similar columns across pages.
  - **Modals:** Overlay containers with forms or confirmations.
  - **Filter/Search Bars:** Input groups with search buttons.
  - **Action Buttons:** Recurring button groups (Edit, Delete, View).
  - **Cards/Grids:** Repeating card layouts for entities.
- For each pattern, note the pages where it appears and the exact markup variations.

### Step 2: Generate Reusable Blade Components
- For each identified pattern, create a Blade component (Laravel 5.7 uses `@component` directive or simple partial includes).
- **Example: Pagination Component**
  - Create `resources/views/components/pagination.blade.php`:
    ```blade
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <span class="relative z-0 inline-flex shadow-sm rounded-md">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5">
                                {!! __('pagination.previous') !!}
                            </span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                {!! __('pagination.previous') !!}
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">{{ $element }}</span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">{{ $page }}</a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                {!! __('pagination.next') !!}
                            </a>
                        @else
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5">
                                {!! __('pagination.next') !!}
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </nav>
    @endif
    ```
- **Example: Delete Confirmation Modal Component**
  - Create `resources/views/components/confirm-delete-modal.blade.php`:
    ```blade
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this <span class="font-weight-bold">{{ $entityName }}</span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    ```

### Step 3: Refactor Existing Views to Use Components
- Scan all Blade files in `resources/views/`.
- Identify inline repetitions of the patterns found in Step 1.
- Replace each occurrence with the corresponding `@include` or `@component`.
- Example replacement for pagination:
  - Before: manual pagination links.
  - After: `@include('components.pagination', ['paginator' => $students])`

### Step 4: Enforce Consistency Rules
- Add a check in Phase 8 (Playwright Debugger) to verify that common UI elements share the same DOM structure and classes.
- If inconsistencies are found (e.g., different pagination markup on different pages), flag as a medium‑severity issue and auto‑fix by replacing with the component.
- Require shared components for:
   - Dropdown styling primitives (reusable class contract).
   - Pagination styling and server-side pagination click logic.
- Preserve performance on high-volume pages by enforcing backend pagination (`page`/`per_page`) and avoiding client-side full-table rendering.

### Step 5: Update Project Memory
- Record all created components and their usage in the runtime context for future runs.

---

# PHASE 2: Database Normalization to 3NF

## Purpose
Analyze the existing database schema, identify 1NF/2NF/3NF violations, and safely refactor to strict 3NF without data loss.

## Tools
- MySQL/PostgreSQL MCP
- CLI / Terminal
- Filesystem (`read_file`, `write_file`, `edit_file`)

## Steps

### Step 0: Migration Inventory & Drift Detection
- Read all files in `database/migrations/`.
- Parse `up()` methods to build expected schema.
- Compare expected schema with actual live database (using `SHOW TABLES`, `SHOW COLUMNS`).
- If discrepancies exist, warn and save drift report to `.security-audits/schema-drift-report.txt`. Continue with live schema.

### Step 1: Schema Audit & Proposal
- Query database for flat structures (e.g., `users` table containing `street`, `city`, `zip`).
- Identify comma-separated values, redundant status strings, missing foreign keys.
- Generate a refactoring plan detailing new tables to create.

### Step 2: The 3‑Step Safe Migration (CRITICAL)
For each normalization, generate a migration following this exact sequence:

**Step A (Up):** Create the new normalized table.
**Step B (Up):** Transfer existing data using `DB::statement()` or `DB::table()->insert()`. Add new foreign key column to old table and populate it.
**Step C (Up):** Only after data is linked, drop the old flat columns using `$table->dropColumn()`.

### Step 3: Model Updating & Relationship Sync
- Use `edit_file` to update affected Eloquent models:
  - Remove old flat attributes from `$fillable`.
  - Add `belongsTo` / `hasMany` relationship methods.
  - Ensure foreign key constraints are defined in migrations (`onDelete('cascade')` where appropriate).

### Step 4: Rollback Script Generation
- Generate a companion SQL rollback file saved to `.security-audits/rollback_YYYYMMDD_HHMMSS.sql`.
- The rollback script should contain reverse operations to restore original flat columns and drop new tables.

### Step 5: 3NF Validation Checklist
- Run SQL queries to verify:
  - No partial dependencies on composite primary keys (2NF violation).
  - No transitive dependencies (3NF violation).
  - All foreign keys properly indexed.
- Save results to `.security-audits/3nf-validation-report.txt`.

### Step 6: Data Verification
- Run a `JOIN` query to verify original data is intact and correctly linked across new tables.

---

# PHASE 3: Codebase Security Hardening

## Purpose
Patch logical vulnerabilities: route protection, mass assignment, SQL injection, CSRF/CORS, file upload validation, sensitive data exposure.

## Tools
- Filesystem (`read_file`, `edit_file`, `search_files`)
- CLI / Terminal

## Steps

### Step 1: Route & Middleware Lockdown
- Read `routes/web.php` and `routes/api.php`.
- Ensure all POST/PUT/DELETE routes and sensitive GET routes are wrapped in `auth` middleware (or `auth:api` for API).
- Use `edit_file` to wrap unprotected routes in `Route::middleware('auth')->group()`.

### Step 2: Controller Authorization Audit
- Scan all files in `app/Http/Controllers`.
- For any method that updates or deletes a resource, ensure `$this->authorize('update', $model)` is called.
- If missing, inject the appropriate Gate check.

### Step 3: Mass Assignment Eradication
- Scan all files in `app/Models`.
- Replace `protected $guarded = [];` with a strict `protected $fillable` array based on database schema.
- Explicitly exclude sensitive columns like `is_admin`, `role_id`.

### Step 4: SQL Injection Eradication
- Search controllers for `DB::raw` or string concatenation in queries.
- Refactor to Eloquent or parameterized bindings: `whereRaw('price > ?', [$request->price])`.

### Step 5: CSRF & CORS Hardening
- Read `app/Http/Middleware/VerifyCsrfToken.php`. Flag any overly permissive `$except` entries.
- Read `config/cors.php` (or CORS middleware). Ensure `allowed_origins` does not contain `*`.

### Step 6: File Upload Validation
- Search controllers for `$request->file()`.
- Ensure every file upload has validation rules: `mimes:jpg,png,pdf|max:2048`. Add if missing.

### Step 7: Sensitive Data Exposure Prevention
- Verify all `User` models have `protected $hidden = ['password', 'remember_token'];`. Add if missing.
- Flag any `->toArray()` calls on models containing sensitive fields.

---

# PHASE 4: Realistic High‑Volume Data Seeding

## Purpose
Populate the 3NF database with thousands of localized, realistic records using Laravel Model Factories.

## Tools
- Filesystem (`write_file`, `read_file`)
- CLI / Terminal
- Playwright MCP (for screenshot verification)

## Steps

### Step 1: Generate/Update Model Factories
- Read `ui-data-contract.json` from `.security-audits/`.
- For each model, create or update a factory in `database/factories/` that defines all required fields using Faker.
- Use `FAKER_LOCALE=en_PH` in `.env` for Philippine locale.

### Step 2: Create/Update DatabaseSeeder
- Write a `DatabaseSeeder.php` that calls factories with counts from config.
- Use `LazyCollection` or `chunk(100)` to avoid memory exhaustion.

### Step 3: Execute Seeding
- Run `php artisan migrate:fresh --seed` (if destructive) or `php artisan db:seed`.
- Display progress bar via CLI output.

### Step 4: Verify Seeded Data in UI
- Use Playwright to navigate to a page displaying seeded data (e.g., Student List).
- Take a screenshot and save to `.security-audits/seeded-ui-screenshot.png`.
- If screenshot shows empty tables, abort and report failure.

---

# PHASE 5: API Security Audit

## Purpose
Audit REST API endpoints for authentication, CORS, IDOR, and rate limiting.

## Tools
- Filesystem
- Playwright MCP
- CLI

## Steps

### Step 1: Route Inventory
- Parse `routes/api.php`. Use `php artisan route:list` to list all routes.
- Classify by method, URI, middleware, presence of `{id}`.
- Save to `.security-audits/api-route-inventory.json`.

### Step 2: Authentication Middleware Check
- For POST/PUT/DELETE or sensitive GET routes, verify `auth:api` middleware is applied.
- Flag unprotected routes; do not auto-modify without confirmation unless in auto-fix mode.

### Step 3: CORS Configuration Audit
- Check `config/cors.php` or middleware.
- Ensure `allowed_origins` does not contain `*`. Suggest fix if needed.

### Step 4: IDOR Fuzzing
- Authenticate as User A. For each ID-parameterized route, attempt to access User B's resource by changing the ID.
- Expect `403` or `404`. If data is returned, flag as CRITICAL.
- Save results to `.security-audits/idor-test-results.json`.

### Step 5: Rate Limiting Verification
- Check if `throttle:60,1` middleware is applied.
- Send 20 rapid requests to a public endpoint. Expect `429` after threshold.
- If missing, suggest adding middleware.

### Step 6: Report
- Compile findings into `.security-audits/api-security-report.md`.

---

# PHASE 6: Authentication Bypass Detection

## Purpose
Test for horizontal/vertical privilege escalation, session fixation, and logout invalidation.

## Tools
- Playwright MCP

## Steps

### Step 1: Role Discovery
- Query roles table or infer from routes. Define matrix: student, teacher, admin.

### Step 2: Horizontal Privilege Escalation
- Log in as student A. Navigate to `/student/profile/1`. Change URL ID to student B.
- Assert: data not shown, redirect or 403/404.

### Step 3: Vertical Privilege Escalation
- As student, attempt to access `/admin`, `/registrar/dashboard`.
- Assert: access denied.

### Step 4: Session Fixation Test
- Capture session cookie before login. Log in. Capture again.
- Assert: cookie value changed.

### Step 5: Logout Session Invalidation
- Log in, capture cookie. Log out. Attempt to use old cookie.
- Assert: request rejected.

### Step 6: Remember Me Token Security (if applicable)
- Test remember me cookie behavior.

### Step 7: Report
- Save to `.security-audits/auth-bypass-report.md`.

---

# PHASE 7: Input Validation Enforcement

## Purpose
Ensure all incoming data is validated using Form Requests. Generate missing classes with strict rules.

## Tools
- Filesystem
- Database MCP
- CLI

## Steps

### Step 1: Controller Input Scan
- Scan `app/Http/Controllers` for `$request->input()`, `$request->all()`, etc.
- Map controller methods to fields used.

### Step 2: Validation Presence Check
- For each method using input, check for Form Request type-hint or `$this->validate()` call.
- Flag missing validation.

### Step 3: Form Request Generation
- Run `php artisan make:request Store{Model}Request`.
- Populate `rules()` using database schema: `string|max:255`, `integer|exists:table,id`, `required`, etc.
- Use `Rule::unique()->ignore()` for updates.

### Step 4: Controller Refactoring
- Type-hint the new Form Request and use `$request->validated()`.

### Step 5: Additional Checks
- File uploads: `file|mimes:...|max:2048`.
- Exclude sensitive fields.

### Step 6: Report
- Save to `.security-audits/validation-enforcer-report.md`.

---

# PHASE 8: Playwright Dual Audit (Debugger + Chaos Tester)

## Purpose
Rigorously test UI/UX and security resilience: mobile layout, XSS/SQLi fuzzing, race conditions, rate limiting, duplicate prevention.

## Tools
- Playwright MCP: all browser tools.

## Part A: Playwright Debugger (UI & Security Fuzzing)

1. **Navigation & Mobile Emulation**
   - Navigate to target URL (from config).
   - Emulate iPhone SE (375×667). Verify viewport meta tag.

2. **Baseline Console**
   - Capture initial console errors.

3. **Snapshot & Interactive Elements**
   - Obtain accessibility tree snapshot.

4. **Mobile Layout & Theme Checks**
   - Check horizontal overflow.
   - Tap targets must be ≥44×44px.

5. **Interaction & Fuzzing**
   - Click each button/link, wait 1000ms.
   - For inputs, inject:
     - XSS: `<img src=x onerror=console.error('XSS_FOUND')>`
     - SQLi: `' OR '1'='1`
   - Monitor console for `XSS_FOUND` and network for SQL errors.

6. **Report**
   - Save findings to `.security-audits/playwright-debug-report.txt`.

## Part B: Playwright Chaos Tester (Stress & Race Conditions)

1. **Auto-Clicker Spam (Duplicate Prevention)**
   - Identify primary submit/save buttons.
   - Inject script to click button 20 times in 1 second.
   - Assert: button disables immediately; only one request succeeds; no duplicate DB records.

2. **Reload-and-Fire Attack**
   - Reload page, immediately click button before idle.
   - Assert: no bypass.

3. **Concurrent Form Submission**
   - Call `form.submit()` 10× in loop.
   - Assert: only one submission processed.

4. **Parameter Tampering & IDOR Probes**
   - Change numeric IDs in URLs. Expect 403/404.

5. **Input Validation Fuzzing**
   - Inject SQLi, XSS, boundary values, special characters into all inputs.

6. **File Upload Abuse**
   - Attempt to upload PHP files, oversized files, double extensions.
   - Assert: rejected.

7. **Rate Limiting**
   - Send 20 rapid requests to login/reset. Expect 429.

8. **Error Disclosure**
   - Scan responses for stack traces, DB errors.

9. **Chaos Report**
   - Save detailed pass/fail to `.security-audits/chaos-test-report.json`.

---

# PHASE 9: Scalability & Performance Audit

## Purpose
Detect N+1 queries, missing indexes, and suggest caching/pagination.

## Tools
- Filesystem
- Database MCP

## Steps

1. **N+1 Query Hunt**
   - Scan controllers for `foreach` containing DB calls. Enforce eager loading (`with()`).

2. **Indexing Check**
   - For columns in `WHERE`, `ORDER BY`, ensure index exists. Generate migration if missing.

3. **Caching Suggestion**
   - Wrap static/heavy queries in `Cache::remember()`.

4. **Pagination Check**
   - Replace `->get()` on large tables with `->paginate(15)`.

5. **Interactive Filter Throughput Hardening (High-Volume UI Lists)**
   - For filter/search UIs backed by large datasets, enforce lightweight request patterns.
   - Do not send heavy metadata/options payloads on every keystroke.
   - Add debounce to text filters/search (target: 350-500ms).
   - Apply a minimum 2-character threshold for broad text search fields.
   - Abort stale in-flight list requests before sending new ones.
   - Lazy-load heavy modal-only options (e.g., full course lists) on first modal open.
   - Prefer text query filters over giant `<select>` lists when option cardinality is high.
   - Add an options mode split for APIs (e.g., `filters` mode returns only light dropdown metadata, `modal` mode returns heavy option lists).
   - Keep active search/filter inputs focus-stable while typing (avoid disabling or remounting the focused control during async reloads).

6. **Report**
   - Save to `.security-audits/performance-audit.txt`.

---

# PHASE 10: Deployment Preflight Check

## Purpose
Final sanity checks before production.

## Tools
- CLI
- Filesystem

## Steps

1. **Environment Configuration**
   - Check `.env`: `APP_ENV=production`, `APP_DEBUG=false`.

2. **Cache Optimization (Laravel 5.7)**
   - Run `php artisan config:cache`
   - Run `php artisan route:cache`
   - (Skip `event:cache` – not available)

3. **Migration Reversibility**
   - Run `php artisan migrate:rollback --pretend`. Verify `down()` methods.

4. **Storage Link**
   - Ensure `public/storage` symlink exists. Run `php artisan storage:link` if missing.

5. **Queue Configuration**
   - Suggest switching from `sync` to `database`/`redis`.

6. **Report**
   - Save to `.security-audits/preflight-report.txt`.

---

# PHASE 11: Persistence & Skill Evolution (Memory Summary)

## Purpose
Create a persistent summary of all important changes made during the run, and offer to embed this knowledge into the skill definition itself so that future runs (even in new chat sessions) can leverage project‑specific context.

## Steps

### Step 1: Gather All Changes and Findings
- Aggregate the following from the `.security-audits/` folder and execution logs:
  - New tables created (from Phase 2).
  - New models/relationships added.
  - Security patches applied (e.g., routes wrapped in auth, mass assignment fixes).
  - Any project‑specific customizations (e.g., custom validation rules, special business logic discovered during UI scan).
  - Unresolved issues that required manual intervention or were skipped.
  - Performance optimizations applied (indexes, eager loading).
  - Playwright/Chaos test results summary (pass/fail counts, critical issues fixed).
  - Reusable Blade components created (from Phase 1.5).

### Step 2: Generate a Concise "Memory Summary"
- Create a markdown file: `.security-audits/PROJECT_MEMORY.md` with the following structure:

```markdown
# Project Memory – {Project Name} – {Date}

## Overview
- Laravel 5.7 / PHP 7.4
- Database: MySQL/PostgreSQL
- Normalization: 3NF applied
- Total records seeded: {count}

## Key Customizations & Important Additions
- **New Tables:** {list}
- **New Relationships:** {list}
- **Custom Validation Rules:** {list}
- **Special Business Logic:** {any discovered during UI scan}
- **Security Patches Applied:** {summary}
- **Reusable Components Created:** {list of Blade components}

## Playwright & Chaos Test Summary
- Debugger: {pass/fail count}
- Chaos: {pass/fail count}
- Duplicate Prevention: {status}
- Rate Limiting: {status}

## Unresolved Issues / Manual Follow‑ups
- {list or "None"}

## Suggested Skill Updates
- {any recommendations for improving future runs, e.g., additional selectors, new fuzzing payloads}
```

### Step 3: Prompt User for Skill Embedding
- Display the summary to the user.
- Ask explicitly:

```
The run has completed. A project memory summary has been saved to `.security-audits/PROJECT_MEMORY.md`.

Do you want to embed this knowledge into the skill definition so that future runs remember these project‑specific details?
[YES] / [NO]
```

### Step 4: If YES – Update the Skill File
- Locate the skill file (the file containing this skill definition).
- Append a new section at the end of the skill file titled `## Project‑Specific Memory (Auto‑Appended)`.
- Insert the content of `PROJECT_MEMORY.md` under that heading, with a timestamp.
- Save the updated skill file.

### Step 5: If NO – Inform User
- Output: `Memory summary saved locally. Skill definition unchanged. You may manually review .security-audits/PROJECT_MEMORY.md for future reference.`

---

# SELF‑HEALING LOOP (Iterate Until Perfect)

## Loop Logic

After completing all phases once, enter verification loop:

```
iteration = 1
max_iterations = config.self_healing.max_iterations (default 5)
all_passed = false

while iteration <= max_iterations and not all_passed:
    1. Re-run Phase 8 (Playwright Dual Audit) and Phase 10 (Preflight).
    2. Collect all failures from reports.
    3. If failures exist:
        a. For each failure, attempt automatic fix based on mapping below.
        b. Log fix applied to `.security-audits/self-healing-log.txt`.
        c. iteration++
    4. Else:
        all_passed = true
```

## Automatic Fix Mapping

| Failure | Auto-Fix Action |
|---------|-----------------|
| Button not disabling on click | Edit Blade view: add `onclick="this.disabled=true; this.form.submit()"` or similar debounce logic. |
| Missing `auth` middleware | Edit `routes/web.php` to wrap route in `Route::middleware('auth')->group()`. |
| XSS vulnerability (payload executed) | Change `{!! $var !!}` to `{{ $var }}` or add `strip_tags` validation. |
| SQLi probe succeeded | Replace `DB::raw` with parameterized binding. |
| Missing index | Generate migration with `$table->index('column')`. |
| `APP_DEBUG=true` | Modify `.env` to set `APP_DEBUG=false`. |
| Migration not reversible | Generate missing `down()` method in migration file. |
| Rate limiting missing | Add `->middleware('throttle:60,1')` to route group. |
| Form validation missing | Generate Form Request class as per Phase 7. |
| UI component inconsistency | Replace divergent markup with the standard Blade component from Phase 1.5. |

## Final Output

### Success
```
PRODUCTION_READY: All verifications passed after {iteration} iteration(s).
Reports and self‑healing log saved in .security-audits/
WAITING_FOR_HUMAN_OK
```

### Failure After Max Iterations
```
PRODUCTION_BLOCKED: Unable to resolve all issues after {max_iterations} iterations.
Unresolved issues:
- [List from latest reports]
See .security-audits/unresolved-issues.md for details.
WAITING_FOR_HUMAN_OK
```

---

# Version Compatibility Guarantee

- **No `composer update` or `npm update`** is ever executed.
- All Artisan commands are compatible with Laravel 5.7.
- PHP syntax used in generated code is PHP 7.4 compliant (no typed properties, no union types).
- Middleware syntax: `'auth'`, `'auth:api'`, `'throttle:60,1'`.

# Execution Note

**This skill is self‑contained.** Execute Phase 0 first to load context, then proceed through all phases in order, run the self‑healing loop, and finally Phase 11 to persist memory.

---

## Project‑Specific Memory (Auto‑Appended)
<!-- This section will be populated with the summary after each run if user opts in -->