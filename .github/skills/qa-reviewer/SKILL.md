---
name: "qa-reviewer"

description: "QA Reviewer  vulnerability scans and legacy-security checks for Laravel 5.7 / PHP 7.1."


# Skill 04: QA Reviewer

##  Purpose
Perform security and QA checks focused on legacy Laravel 5.7 / PHP 7.1 code: vulnerability scanning, header enforcement, session/security settings, and safe file-upload rules.

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery. (Use `ml-auto`/`mr-auto`, NOT `ms-auto`).
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.
- **Auth:** `php artisan make:auth` (Laravel 5.7 standard)  DO NOT use Breeze/Jetstream

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Vulnerability Scan (priority checks)
- IDOR (Insecure Direct Object Reference)
  - Verify authorization on every resource access (use policies / `authorize()` / owner checks).
  - Test endpoints by swapping resource IDs to confirm access is denied when ownership/permission differs.
  - Code checklist: ensure controllers call `$this->authorize('view', $model)` or equivalent, or filter queries by owner (`->where('user_id', auth()->id())`).

- SQL Injection
  - Ensure all DB queries use parameter binding / Eloquent / Query Builder. Flag raw string concatenation.
  - Check for `DB::raw()` usage and verify parameters are bound or sanitized.
  - Automated test: attempt injection payloads on input fields and verify no unexpected query behavior.

- CSRF
  - Verify all HTML forms include `@csrf`.
  - Verify `VerifyCsrfToken` middleware enabled for state-changing routes.
  - AJAX: confirm `X-CSRF-TOKEN` header is set by client and validated server-side.

- XSS (Cross-Site Scripting)
  - Ensure output is escaped with `{{ }}` by default; flag use of `{!! !!}` and verify sanitization.
  - For HTML-rich inputs, require server-side sanitization (e.g., HTML Purifier) and strict allowed tags.
  - Test: submit script payloads in inputs and verify rendered pages escape or remove payloads.

##  Legacy Security
- Password hashing
  - Ensure `Hash::make($password)` (bcrypt) is used for passwords.
  - Verify `config/hashing.php` driver is `bcrypt` and iterations/cost are reasonable for PHP 7.1.
  - Check no plaintext passwords stored in DB or logs.

- Session timeout
  - Require `config/session.php` to set `lifetime` => 30 (minutes) for 30-minute timeout.
  - Recommend `expire_on_close` as appropriate; verify session invalidation after timeout in tests.

##  HTTP Security Headers
- `X-Frame-Options`
  - Set to `SAMEORIGIN` or `DENY` to prevent clickjacking.
  - Example middleware snippet:
    ```php
    public function handle($request, Closure $next) {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        return $response;
    }
