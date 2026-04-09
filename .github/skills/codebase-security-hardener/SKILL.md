---
name: "codebase-security-hardener"
description: "Retrofits security onto an existing Laravel codebase. Scans for and fixes missing route middleware, mass-assignment vulnerabilities, raw SQL injections, CSRF misconfigurations, file upload flaws, and sensitive data exposure."
---

# Codebase Security Hardener

## Purpose
Scan an already-built application to detect and patch logical vulnerabilities, broken access control, injection flaws, and data exposure without altering the frontend UI.

## Required MCP Tools
- Filesystem (`read_file`, `edit_file`, `search_files`)
- CLI / Terminal

## Workflow (Steps 1–7)

1) **Route & Middleware Lockdown**
- Read `routes/web.php` and `routes/api.php`.
- **Action:** Ensure all routes that modify data (POST, PUT, DELETE) or access sensitive views are wrapped in `auth` middleware. If unprotected sensitive routes exist, use `edit_file` to wrap them in `Route::middleware('auth')->group()`.

2) **Controller Authorization Audit**
- Scan all files in `app/Http/Controllers`.
- **Action:** For any method that updates or deletes a specific resource (e.g., `update(Request $request, User $user)`), inject `$this->authorize('update', $user);` or equivalent Gate checks if they are missing.

3) **Mass Assignment Eradication**
- Scan all files in `app/Models`.
- **Action:** If a model uses `protected $guarded = [];`, use `edit_file` to replace it with a strict `protected $fillable = ['field_1', 'field_2'];` array based on the database schema, explicitly excluding sensitive columns like `is_admin` or `role_id`.

4) **SQL Injection Eradication**
- Search controllers for `DB::raw` or string concatenation in queries.
- **Action:** Refactor them to use Eloquent or parameterized bindings (e.g., `whereRaw('price > ?', [$request->price])`).

5) **CSRF & CORS Hardening**
- Read `app/Http/Middleware/VerifyCsrfToken.php`.
- **Action:** Flag any routes in `$except` that should not be excluded. Suggest removal of overly permissive exceptions.
- Read `config/cors.php`.
- **Action:** Ensure `allowed_origins` does not contain `'*'` in production. Replace with specific trusted domains.

6) **File Upload Validation**
- Search controllers for `$request->file()` usage.
- **Action:** Ensure every file upload has validation rules including `mimes:jpg,png,pdf` and `max:2048`. Add validation if missing.

7) **Sensitive Data Exposure Prevention**
- Scan all API and JSON responses for accidental inclusion of `password`, `remember_token`, or `api_token`.
- **Action:** Verify that all User models have `protected $hidden = ['password', 'remember_token'];`. Add if missing. Flag any `->toArray()` calls on models containing sensitive fields.

## STOP COMMAND
When all codebase patches are applied, output exactly:
WAITING_FOR_HUMAN_OK