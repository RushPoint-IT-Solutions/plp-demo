---
name: "api-security-auditor"
description: "Audits REST API endpoints for Laravel 5.7: proper authentication, rate limiting (throttle:60,1), CORS, and IDOR. Tests endpoints programmatically via Playwright or HTTP."
---

# API Security Auditor (Laravel 5.7)

## Purpose
Ensure all API endpoints are protected against unauthorized access, information disclosure, and abuse. Compatible with Laravel 5.7 / PHP 7.4.

## Required MCP Tools
- Filesystem (`read_file`, `search_files`)
- Playwright MCP (for authenticated testing)
- CLI / Terminal

## Workflow (Steps 1–6)

### 1) Route Inventory & Classification
- Read `routes/api.php` and any included route files.
- Use `php artisan route:list` to obtain route list. (Laravel 5.7 supports `--path` and `--method` filters.)
- **Action:** Classify each route by:
  - HTTP method (GET, POST, PUT, DELETE)
  - URI pattern
  - Assigned middleware
  - Whether it contains a resource ID parameter (e.g., `{id}`)
- **Output:** Save the classified list to `.security-audits/api-route-inventory.json`.

### 2) Authentication Middleware Check
- For each route that modifies data (POST, PUT, PATCH, DELETE) or returns sensitive information (GET on user‑specific resources):
  - Verify the route is protected by `auth:api` middleware. (Laravel 5.7 uses `auth:api` for token/api guard.)
- **Action:** Flag any sensitive route missing `auth:api`. If found, suggest wrapping in `Route::middleware('auth:api')->group()`.
- **Critical:** Do NOT automatically modify API routes—only report. The user must approve changes.

### 3) CORS Configuration Audit
- Laravel 5.7 may use `barryvdh/laravel-cors` or manual headers. Check `config/cors.php` if exists, else check middleware.
- **Checks:**
  - `allowedOrigins` must not be `['*']` in production.
  - `allowedMethods` should be restricted to `['GET', 'POST', 'PUT', 'DELETE']`.
  - `allowedHeaders` should be explicit.
  - `supportsCredentials` should be `true` only if needed.
- **Action:** If misconfigurations exist, generate a suggested corrected config block and save to `.security-audits/cors-fix-suggestion.txt`.

### 4) IDOR (Insecure Direct Object Reference) Fuzzing
- Identify all routes that include a resource ID in the URI (e.g., `/api/users/{id}`).
- **Testing Methodology:**
  - Authenticate as User A (e.g., student ID 5).
  - For each ID‑parameterized route, attempt to access a resource belonging to User B (e.g., change `{id}` from 5 to 6).
  - Use Playwright or direct `fetch` via `browser_evaluate` to make the request with User A's token.
- **Expected Behavior:**
  - Server returns `403 Forbidden` or `404 Not Found` (to avoid information disclosure).
  - Server does **not** return data belonging to User B.
- **Action:** Record any endpoint that returns another user's data as a **CRITICAL** finding.
- **Output:** IDOR test results saved to `.security-audits/idor-test-results.json`.

### 5) Rate Limiting Verification
- Laravel 5.7 uses `throttle:60,1` (60 attempts per minute). Check if `throttle` middleware is applied to the API route group.
- **Testing Methodology:**
  - Select a public endpoint (e.g., `/api/login`, `/api/register`).
  - Send 10–20 rapid requests in succession using `fetch` in a loop via `browser_evaluate`.
- **Expected Behavior:**
  - After exceeding the limit (usually 60 per minute), server returns `429 Too Many Requests`.
- **Action:** If no rate limiting is detected, suggest adding `->middleware('throttle:60,1')` to the API route group.
- **Output:** Rate limiting test result saved.

### 6) Comprehensive Report
- Compile all findings into a single markdown report: `.security-audits/api-security-report.md`.
- Include:
  - Summary of issues by severity (Critical, High, Medium, Low).
  - Detailed findings with route name, issue description, and remediation steps.
  - CORS fix suggestion if needed.
- **Action:** Do not apply fixes automatically; await user confirmation unless running in fully automated mode (configurable).

## STOP COMMAND
When audit is complete and report is saved, output exactly:
```
API_SECURITY_AUDIT_COMPLETE
WAITING_FOR_HUMAN_OK
```