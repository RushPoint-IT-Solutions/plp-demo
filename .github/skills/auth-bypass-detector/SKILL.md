---
name: "auth-bypass-detector"
description: "Tests for privilege escalation, horizontal/vertical access control bypass, session fixation, and improper session invalidation. Laravel 5.7 compatible."
---

# Authentication Bypass Detector

## Purpose
Verify that the application's authorization logic correctly restricts users to their own data and role‑appropriate actions. Detect vulnerabilities where a lower‑privileged user can access or modify resources belonging to others or to higher roles.

## Required MCP Tools
- Playwright MCP (`browser_navigate`, `browser_evaluate`, `browser_click`, `browser_wait_for`)
- Filesystem (to read role definitions if available)

## Pre‑requisites
- At least two test user accounts with different roles (e.g., `student@school.test` and `teacher@school.test`) and known passwords.
- The application must have identifiable role‑specific routes (e.g., `/admin`, `/registrar`, `/student/dashboard`).

## Workflow (Steps 1–7)

### 1) Role Discovery
- If a `roles` table or enum exists, query it via Database MCP.
- Alternatively, infer roles from routes or from provided test credentials.
- **Action:** Define a role matrix:
  - `student`: can access own profile, grades, enrollment.
  - `teacher`: can access own classes, student lists (limited).
  - `admin`: full access.

### 2) Horizontal Privilege Escalation Test
- Log in as `student_a` (ID 1).
- Navigate to a resource that displays data belonging to `student_a` (e.g., `/student/profile/1`).
- Capture the page content.
- Now, manually change the URL ID parameter to that of `student_b` (ID 2) and navigate.
- **Assertions:**
  - The application must **not** display `student_b`'s data.
  - It should either redirect away, show a 403 error, or display a "not found" message (404 is acceptable for security).
- Repeat for any resource that uses numeric IDs (grades, applications, payments).
- **Output:** Record any successful horizontal access as a **HIGH** severity finding.

### 3) Vertical Privilege Escalation Test
- Log in as `student`.
- Attempt to directly navigate to known admin or teacher routes:
  - `/admin`
  - `/admin/users`
  - `/registrar/dashboard`
  - `/teacher/grade-entry`
- Use `browser_navigate` for each route.
- **Assertions:**
  - Access must be denied (403 or redirect to login/home).
  - No admin functionality should be visible or executable.
- **Output:** Record any successful access as a **CRITICAL** finding.

### 4) Session Fixation Test
- Before logging in, use `browser_evaluate` to read the current session cookie value (e.g., `document.cookie`).
- Log in with valid credentials.
- After login, again read the session cookie value.
- **Assertions:**
  - The session cookie value **must change** after successful authentication (Laravel regenerates session ID by default).
  - The old session ID should no longer be valid.
- **Action:** If the session ID remains unchanged, flag as **HIGH** (session fixation vulnerability).

### 5) Logout Session Invalidation Test
- Log in, capture the session cookie.
- Perform logout action (click logout button).
- Attempt to use the captured session cookie in a new request (via `browser_evaluate` fetch with credentials: 'include').
- **Assertions:**
  - The server must reject the request (redirect to login or 401).
  - The session must be completely destroyed server‑side.
- **Output:** If the old session remains valid after logout, flag as **CRITICAL**.

### 6) Remember Me Token Security (if applicable)
- If the login form has a "Remember Me" checkbox:
  - Log in with "Remember Me" checked.
  - Capture the `remember_web_*` cookie.
  - Close the browser context (simulate browser close) and create a new context.
  - Set the captured remember cookie and navigate to a protected page.
- **Assertions:**
  - User should be automatically logged in.
  - The remember token should be long, random, and stored hashed in the database.
- **Action:** This is informational; Laravel 5.7's built‑in remember me is secure if properly configured. Check `config/auth.php` to ensure `users` provider uses `EloquentUserProvider`.

### 7) Comprehensive Report
- Compile all test results into `.security-audits/auth-bypass-report.md`.
- Include:
  - Test credentials used.
  - Step‑by‑step reproduction for any vulnerability found.
  - Severity rating and remediation guidance.
- **Action:** Do not automatically fix auth logic; provide clear instructions for the developer.

## STOP COMMAND
When all tests complete, output exactly:
```
AUTH_BYPASS_AUDIT_COMPLETE
WAITING_FOR_HUMAN_OK
```