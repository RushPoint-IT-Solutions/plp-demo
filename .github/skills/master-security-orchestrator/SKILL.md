---
name: "master-security-orchestrator"
description: "Executes Defense-in-Depth pipeline. Scans backend, fuzzes frontend, stress-tests servers, AUTO-FIXES vulnerabilities, and stores all reports in a git-ignored '.security-audits/' folder."
---

# Master Security Orchestrator (Active Defender & Cleaner)

## Purpose
Coordinate the execution of backend static analysis, frontend UI/security fuzzing, and stress testing. This skill must automatically patch discovered vulnerabilities using filesystem tools and ensure all generated logs, traces, and reports are confined to a dedicated, git-ignored folder to prevent workspace clutter.

## Required MCP Tools
- `read_file`, `edit_file`, `delete_file`
- CLI / Terminal (for `git` commands, directory creation, compiling)
- Playwright tools (for Frontend & Chaos testing)

## Workflow (Steps 1–6)

1) **Get Target Scope**
- Prompt the user for: Target URL and Target Local Directory.

2) **Phase 1: Backend Audit & AUTO-FIX**
- Scan `./app/Http/Controllers` and `./app/Models`.
- **Action:** Use `edit_file` to immediately patch found vulnerabilities (e.g., inject `$this->authorize()`, define `$fillable`).
- *Store a log of what was fixed in memory.*

3) **Phase 2: Frontend UI & Injection Check**
- Execute UI/Mobile checks and fuzz inputs for XSS/SQLi.
- **Action:** If CSS/layout issues are found, use the CLI to `grep` the exact class, use `edit_file` to fix the style, and recompile.

4) **Phase 3: Chaos & Stress Testing**
- Run the aggressive auto-clicker and rapid-reload scripts on primary action buttons to test for Race Conditions and missing Rate Limits.

5) **Phase 4: Workspace Organization & Gitignore (CRITICAL)**
- **Create Directory:** Use the CLI to create a hidden folder in the project root: `mkdir -p .security-audits`
- **Gitignore the Folder:** Ensure this folder is ignored by version control. Use the CLI to run: 
  `grep -qxF '/.security-audits/' .gitignore || echo '/.security-audits/' >> .gitignore`
- **Move/Delete Junk:** Move any Playwright trace files or JSON logs into `.security-audits/`. Use `delete_file` or CLI `rm` to destroy any leftover temporary files in the root.

6) **Generate Final Master Report**
- Output the final master report locally inside the new folder as: `.security-audits/master-audit-report.md`
- The report must contain:
  - **Patches Applied:** Exact files modified and the vulnerabilities fixed.
  - **Remaining Threats:** Anything the AI could not auto-fix safely.
  - **Cleanup Confirmation:** A brief note confirming all logs are safely stored in `.security-audits/` and the folder is git-ignored.

## STOP COMMAND
When the report is generated in the target folder and cleanup is complete, output exactly:
WAITING_FOR_HUMAN_OK