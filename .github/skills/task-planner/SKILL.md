---
name: "task-planner"
description: "Task Planner  Architect role: produces Job Sheets for Worker AI."
applyTo:
  - "**/*"
version: "1.0-legacy"

---

# Skill 01: Task Planner (Architect Role)

##  Purpose
Big AI creates Job Sheets. Small AI executes one step at a time.

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery. (Use `ml-auto`/`mr-auto`, NOT `ms-auto`).
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev` or `npm run watch`. NO Vite.
- **Database:** XAMPP MySQL.

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Authentication
- Use: `php artisan make:auth` (Laravel 5.7 standard)
- DO NOT use: Laravel Breeze, Jetstream, or newer scaffolding packages

##  MCP Integration (Required for Every Job Sheet)
- **Filesystem MCP:** Scan project structure before generating code; verify target files exist and report exact paths/line numbers.
- **MySQL MCP:** Verify database schema before running migrations or seeding (e.g., required tables/columns exist).
- **Playwright MCP (optional):** Run basic UI checks (responsive breakpoints, header presence, XSS test flows).
- Worker MUST report findings with exact file paths, line numbers, and sample requests/payloads used.

##  Context Router
For every step, specify exactly 1 or 2 `.github/skills/` files the Worker is allowed to read.

##  Job Sheet Template
---
##  JOB SHEET: [Task Name]
### Step X/XX: [Action Verb + Target File]
- **File:** `#path/to/file.ext`
- **Required Skills:** `.github/skills/03-laravel-integrator.md`
- **Command/Action:** [Exact code change]
- **Expected Output / Verification:** [What success looks like; tests or commands to run]
- **STOP:** WAITING_FOR_HUMAN_OK
---

##  Escalation Protocol
- If MCP checks fail (missing files/tables/columns), STOP and output:
` ESCALATION REQUIRED. Missing file/table/column: <detail>. Architect review needed.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
