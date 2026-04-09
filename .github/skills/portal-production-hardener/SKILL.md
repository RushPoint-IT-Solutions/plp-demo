---
name: "portal-production-hardener"
description: "Self‑healing master orchestrator for Laravel 5.7 / PHP 7.4. Iterates until all Playwright and MCP verifications pass. Full lifecycle: UI scan → 3NF normalization → security hardening → seeding → API audit → auth bypass → input validation → dual Playwright audit → performance → preflight check."
---

# Portal Production Hardener (Self‑Healing) – Laravel 5.7

## Purpose
Transform a development Laravel 5.7 project into a production‑ready, secure, and scalable school portal. **Will not stop until all verifications pass** (up to a configurable maximum iteration limit).

## Prerequisites
- Laravel 5.7 project with database connection configured.
- Playwright MCP server accessible.
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

## Workflow (Iterative Until Perfection)

The master skill runs in two stages:

### Stage 1: Foundational Setup (Run Once)
These phases are deterministic and only need to execute once.

1. **Phase 1: UI Data Mapping** → Execute `ui-to-seeder-mapper`.  
2. **Phase 2: Database Normalization (3NF)** → Execute `db-retrofit-normalizer`.  
3. **Phase 3: Codebase Security Hardening** → Execute `codebase-security-hardener`.  
4. **Phase 4: Realistic Data Seeding** → Execute `high-volume-trash-seeder`.  
5. **Phase 4.5: Post-Seed Pagination/Index Gate** → Verify seeded list pages use server-side pagination and indexed query paths before continuing.  
5. **Phase 5: API Security Audit** → Execute `api-security-auditor`.  
6. **Phase 6: Authentication Bypass Testing** → Execute `auth-bypass-detector`.  
7. **Phase 7: Input Validation Enforcement** → Execute `input-validation-enforcer`.  
8. **Phase 9: Performance & Scalability Audit** → Execute `scalability-performance-auditor`.  

### Stage 2: Verification & Self‑Healing Loop (Iterate Until Pass)

**Loop Control Variables:**
- `iteration = 1`
- `max_iterations` from config (default 5)
- `all_passed = false`

**Loop Body:**

1. **Phase 8: Playwright Dual Audit**
   - Execute `playwright-debugger`
   - Execute `playwright-chaos-tester`
   - Capture all failures from both reports.

2. **Phase 10: Deployment Preflight Check**
   - Execute `deployment-preflight-check`
   - Capture any failures.

3. **Evaluate Results**
   - If **zero failures** across Playwright and Preflight:
     - Set `all_passed = true`
     - Break out of loop.
   - If failures exist and `iteration < max_iterations`:
     - **Attempt Automatic Fixes:**
       - For Playwright failures (e.g., missing `disabled` attribute on button, XSS vulnerability, missing rate limit), call the appropriate sub‑skill with a `--fix` flag or apply targeted patches.
       - For Preflight failures (e.g., `APP_DEBUG=true`), automatically modify `.env` or config files.
     - Log all fixes applied to `.security-audits/self-healing-log.txt`.
     - Increment `iteration`.
     - **Re‑run only the failed verification phases** (or full Stage 2).
   - If `iteration == max_iterations` and failures persist:
     - Halt and output a detailed report of unresolved issues.

### Automatic Fix Mapping

| Failure Type | Automatic Fix Action |
|--------------|----------------------|
| Button not disabling on click | Inject JavaScript debouncing/throttling code into relevant Blade view. |
| Missing `auth` middleware on route | Edit `routes/web.php` to wrap route in `Route::middleware('auth')->group()`. |
| XSS vulnerability detected | Apply `{{ }}` escaping (if using raw `{!! !!}`), or add `strip_tags` validation. |
| SQLi probe succeeded | Replace `DB::raw` with parameterized bindings. |
| Missing index on foreign key | Generate a new migration with `$table->index('column_name')`. |
| Large seeded table causes page/search lag | Implement server-side pagination (`page`, `per_page`), move search/sort to backend, and add composite indexes for filter/order columns. |
| `APP_DEBUG=true` in production | Modify `.env` file to set `APP_DEBUG=false`. |
| Migration not reversible | Generate missing `down()` method in migration file. |
| Rate limiting missing | Add `->middleware('throttle:60,1')` to route group. |

### Final Output

When `all_passed == true`:
```
PRODUCTION_READY: All verifications passed after {iteration} iteration(s).
Reports and self‑healing log saved in .security-audits/
WAITING_FOR_HUMAN_OK
```

When `max_iterations` reached with failures:
```
PRODUCTION_BLOCKED: Unable to resolve all issues after {max_iterations} iterations.
See .security-audits/unresolved-issues.md for details.
WAITING_FOR_HUMAN_OK
```

## Version Compatibility Guarantee
- No `composer update` or `npm update` is ever executed.
- Commands restricted to Laravel 5.7 available Artisan commands.
- All generated code uses PHP 7.4 syntax.