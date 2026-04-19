---
name: "playwright-chaos-tester"
description: "Aggressive security & resilience stress-testing. Simulates auto-clickers, rapid reloads, parameter tampering, IDOR, file upload abuse, XSS/SQLi fuzzing, concurrency attacks, and error disclosure detection. Verifies duplicate-prevention and rate-limiting."
---

# Playwright Chaos & Stress Tester

## Purpose
Use the Playwright MCP server to aggressively test a web page's resilience against malicious user behavior, covering:

- Race conditions & duplicate submissions (auto‑clickers, reload‑and‑fire)
- Parameter tampering (IDOR, privilege escalation)
- Input validation bypass (SQLi probes, XSS vectors)
- File upload abuse (malicious extensions, oversized files)
- Rate limiting & brute‑force protection
- Error message information disclosure
- Business logic flaws (negative values, skipped steps)

**WARNING: This is an aggressive testing routine. Ensure the target server is a staging or local environment.**

## Required MCP Tools
- `browser_navigate`
- `browser_console_messages`
- `browser_evaluate`
- `browser_click`
- `browser_type`
- `browser_file_upload`
- `browser_wait_for`

## Workflow (Steps 1–10)

### 1) Get Inputs & Auto‑Discovery
- Prompt for: Target URL, authentication credentials (if needed), which modules to test (default: all), and **User Role** (one of `student`, `faculty`, `applicant`, `registrar`; default: `student`).

### Credentials (Default mapping)

- student: username `student`, password `student`
- faculty: username `faculty`, password `faculty`
- applicant: username `applicant`, password `applicant`
- registrar: username `admin`, password `password`

If explicit credentials are supplied as inputs, use them instead of the defaults.

### Pre-Navigation & Login Strategy

- Before interacting with forms or fuzzing, attempt to `browser_navigate` to the Target URL exactly as provided. If the navigation is redirected to a login page, detect the login form and perform login using the selected role's credentials.
- When the site always loads a shared login entry (for example, the student login page), the agent should still attempt to reach the Target URL first; after any redirect to login, sign in with the requested role, then re-navigate to the Target URL to confirm access and continue testing.
- If the agent detects an existing authenticated session for a different user, prefer to sign out (if sign-out link/button available) and sign in as the requested role to ensure correct test scoping.

These pre-navigation steps reduce false positives caused by unintended default sessions and ensure the chaos tests exercise the intended role's surface area.
- Auto‑detect interactive elements:
  - Buttons matching: `save`, `submit`, `next`, `approve`, `confirm`, `update`, `send`, `delete`, `publish`
  - Forms with inputs
  - File upload fields (`input[type="file"]`)
  - Links with numeric IDs in URLs (potential IDOR targets)

### 2) Baseline & Navigation
- Navigate to the Target URL. Log in if credentials provided.
- Clear `browser_console_messages` and capture initial console state.

### 3) Phase 1: Auto‑Clicker Spam (Race Condition & Duplicate Prevention)
- Identify primary action buttons (priority: `[type="submit"]`, text contains `save|submit|next|approve|confirm|update`).
- For each button:
  - Inject a script to click the button 20 times within 1 second (spaced by 50ms).
  - Wait 2000ms.
  - **Assertions:**
    - Button becomes disabled immediately after first click.
    - At most **one** successful request is accepted.
    - Server returns `409`, `422`, `429`, or similar duplicate‑blocking response.
    - No duplicate database records are created.

### 4) Phase 2: Reload‑and‑Fire Attack
- Reload the page via `browser_navigate`.
- **Immediately** (before network idle) execute a click on the target button via `browser_evaluate`.
- Wait 2000ms.
- **Assertion:** The premature click does **not** bypass frontend guards. No unintended state change occurs.

### 5) Phase 3: Concurrent Form Submission
- For each form:
  - Fill fields with valid data.
  - Use `browser_evaluate` to call `form.submit()` 10 times in a tight loop.
- **Assertion:** Only one submission is processed; others are blocked.

### 6) Phase 4: Parameter Tampering & IDOR Probes
- Identify all links/buttons that contain numeric IDs in the URL or request payload (e.g., `/users/123/edit`).
- For each ID parameter:
  - Replace with another plausible ID (e.g., increment/decrement by 1, use `1`, `0`, `-1`, `999999`).
  - Navigate to the tampered URL or modify the request payload (via `browser_evaluate` fetch override if needed).
- **Assertions:**
  - Access is denied (`403` or `404`) for resources belonging to other users.
  - No information leakage about other records.

### 7) Phase 5: Input Validation & Injection Fuzzing
- For every input field (text, email, number, search, etc.):
  - **SQLi Probe:** Inject `' OR '1'='1`, `'; DROP TABLE users; --`, and `1' AND SLEEP(2) --`.
  - **XSS Probe:** Inject `<img src=x onerror=console.error('XSS_FOUND')>`, `<script>console.error('XSS')</script>`, and `javascript:alert(1)`.
  - **Boundary Values:** For numeric fields, input `-1`, `0`, `9999999999`, and non‑numeric strings.
  - **Special Characters:** Input `../../../../etc/passwd`, `%00`, null bytes.
  - Submit the form after each injection.
- **Assertions:**
  - No SQL syntax errors appear in console/network.
  - No XSS payloads execute (monitor console for `XSS_FOUND`).
  - Inputs are properly sanitized or rejected with validation errors.

### 8) Phase 6: File Upload Abuse
- Locate all `input[type="file"]` fields.
- Attempt to upload:
  - A PHP file with extension `.php`, `.phtml`, `.php5` (if allowed, rename a harmless text file).
  - An oversized file (e.g., 100MB dummy file, if server allows).
  - A file with double extension (`malicious.php.jpg`).
  - An empty file.
- **Assertions:**
  - Server rejects dangerous extensions.
  - Server enforces size limits.
  - Uploaded files are not stored in web‑accessible locations or are renamed with random names.

### 9) Phase 7: Rate Limiting & Brute‑Force Detection
- For login, password reset, or any unauthenticated endpoint:
  - Send 10 rapid requests (using `fetch` in `browser_evaluate` or repeated navigation).
- **Assertion:** After a threshold (e.g., 5 attempts), server returns `429 Too Many Requests` or introduces a delay.

### 10) Phase 8: Error Disclosure & Cleanup
- Scan all network responses and console messages for:
  - Stack traces containing file paths (`/var/www/`, `vendor/laravel`).
  - Database error messages (`SQLSTATE`, `MySQL`, `ORA-`).
  - Debug output (`Whoops!`, `Debugbar`).
- **Assertion:** No sensitive information is exposed to the client.
- Generate a comprehensive Chaos Report saved to `.security-audits/chaos-test-report.json` including:
  - Pass/fail status for each phase
  - Details of any vulnerability found (with reproduction steps)
- Clear `localStorage`, `sessionStorage`, cookies, and reload.

## STOP COMMAND
When cleanup is complete, output exactly:
WAITING_FOR_HUMAN_OK