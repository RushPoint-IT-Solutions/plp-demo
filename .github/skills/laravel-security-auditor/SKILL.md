---
name: "laravel-security-auditor"
description: "Static Application Security Testing (SAST) tool. Scans backend controllers, models, and routes for authorization flaws, raw SQL injections, mass assignment, and exposed secrets."
---

# Laravel Backend Security Auditor

## Purpose
Use the Filesystem MCP to read the raw backend code and identify structural vulnerabilities, logical flaws, and insecure configurations that frontend testing cannot detect.

## Required MCP Tools
- `read_file`
- `list_directory`
- `search_files` (if available)

## Workflow (Steps 1–5)

1) **Get Inputs**
- Prompt the user for: Target Directory (Default: `./app/Http/Controllers` and `./app/Models`).

2) **Phase 1: Authorization & Privilege Escalation Check**
- Scan Controller files for authorization gates.
- **Flag as CRITICAL:** Any controller method handling sensitive data (e.g., `update`, `delete`, `destroy`, `show`) that does NOT implement `$this->authorize()`, `Gate::allows()`, or use route-level middleware (like `auth` or role-based middleware).
- *Reasoning:* If authorization is missing, an authenticated 'Student' might be able to manually construct a POST request to a 'Registrar' function.

3) **Phase 2: Mass Assignment & ORM Vulnerabilities**
- Scan Model files (`./app/Models`).
- **Flag as WARNING:** Models that use `protected $guarded = [];` without explicit validation in the corresponding controller.
- *Reasoning:* Malicious users can inject unexpected fields (like `is_admin = true`) into a form payload, which the ORM will blindly save to the database. Ensure `$fillable` is strictly defined.

4) **Phase 3: Raw SQL Injection Detection**
- Scan Controllers and Repositories for raw database queries.
- **Search for:** `DB::raw()`, `->whereRaw()`, `->orderByRaw()`.
- **Flag as CRITICAL:** If user input (e.g., `$request->input()`, `$request->query()`) is directly concatenated into a raw SQL string instead of using parameterized bindings `(?)`.

5) **Phase 4: Configuration & Secrets Audit**
- Read `.env` (if permitted) and `config/app.php`.
- **Flag as CRITICAL:** `APP_DEBUG=true` in a production-like environment configuration.
- **Flag as WARNING:** Hardcoded API keys or passwords directly inside controller files instead of referencing `env()`.

## Aggregated Security Report
Compile findings into a structured Code Audit Report. For every vulnerability found, provide:
1. **File Path & Line Number** (approximate).
2. **Vulnerability Type** (e.g., Broken Access Control, Mass Assignment).
3. **Severity** (Critical, High, Warning).
4. **Remediation Code Snippet:** Write the exact PHP/Laravel code needed to fix the vulnerability securely.

## STOP COMMAND
When the audit report is delivered, output exactly:
WAITING_FOR_HUMAN_OK