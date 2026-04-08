---
name: "master-security-orchestrator"
description: "Executes the full Defense-in-Depth pipeline. Runs the Static Code Auditor, UI/Security Debugger, and Chaos Tester sequentially, compiling a master vulnerability report."
---

# Master Security Orchestrator (Defense-in-Depth)

## Purpose
Coordinate the execution of backend static analysis, frontend UI/security fuzzing, and aggressive stress testing into a single, automated workflow.

## Required MCP Tools
- Filesystem / File Editor (for Code Audit)
- CLI / Terminal (for compiling/commands)
- Playwright (for Frontend & Chaos testing)

## Workflow (Steps 1–5)

1) **Get Target Scope**
- Prompt the user for: Target URL and Target Local Directory (for backend code).

2) **Phase 1: Backend Static Audit (The Foundation)**
- Execute the `laravel-security-auditor` workflow.
- Scan `./app/Http/Controllers` and `./app/Models` for missing authorization gates, Mass Assignment vulnerabilities, and raw SQL injections.
- *Store findings in memory.* Do not stop the master workflow.

3) **Phase 2: Frontend UI & Injection Check (The Surface)**
- Execute the `playwright-debugger` workflow on the Target URL.
- Emulate iPhone SE layout.
- Inject safe XSS (`<img src="x" onerror="console.error('CRITICAL_XSS')">`) and SQLi (`' OR '1'='1`) payloads into all discovered input fields.
- *Store findings in memory.*

4) **Phase 3: Chaos & Stress Testing (The Resilience)**
- Execute the `playwright-chaos-tester` workflow on the primary action button of the Target URL.
- Inject JS to spam clicks (20x/sec) and attempt rapid form submissions to test Rate Limiting (429s) and Race Conditions.
- *Store findings in memory.*

5) **Generate the Master Health Report**
- Consolidate all stored findings into a single, comprehensive Markdown report categorized strictly by:
  - **CRITICAL:** (e.g., missing backend auth, successful XSS, race conditions allowing duplicate database entries).
  - **WARNING:** (e.g., missing rate limits, mass assignment risks).
  - **UI/UX FAILURES:** (e.g., broken iPhone SE layout, missing tap targets).
- For every backend/CSS issue, provide the exact file path and suggested code fix.

## STOP COMMAND
When the master report is delivered, output exactly:
WAITING_FOR_HUMAN_OK