---
name: "playwright-debugger"
description: "Strict UI/Mobile tester and Security Fuzzer. Checks iPhone SE layouts, tap-target sizes, CSS theme consistency, and tests for XSS/SQLi vulnerabilities."
---

# Playwright Debugger – Strict UI & Security Enforcer

## Purpose
Use the Playwright MCP server to perform a rigorous audit of a web page's frontend. This includes strict mobile layout checks (iPhone SE), theme consistency, and security fuzzing (XSS, SQLi). 

## Required MCP Tools
- `browser_navigate`
- `browser_console_messages`
- `browser_snapshot`
- `browser_click`
- `browser_type`
- `browser_evaluate`
- `browser_wait_for`

## General Safety Throttle
To prevent accidental server strain during normal debugging, wait at least 1000ms (`browser_wait_for`) between every navigation and interaction.

## Workflow (Steps 1–9)

1) **Get Inputs** - Prompt for: Target URL, Emulate Mobile (Default: Yes), Enable Security Fuzzing (Default: Yes), and **User Role** (one of `student`, `faculty`, `applicant`, `registrar`; default: `student`).

## Credentials (Default mapping)

- student: username `student`, password `student`
- faculty: username `faculty`, password `faculty`
- applicant: username `applicant`, password `applicant`
- registrar: username `admin`, password `password`

These are the default credentials the agent should try when the target page requires authentication. If the user supplies explicit credentials, prefer those.

## Pre-Navigation & Login Strategy

- Always attempt to navigate directly to the requested Target URL first using `browser_navigate`. The goal is to reach the actual target page before deciding to log in.
- If navigation to the Target URL redirects to a login page or shows a login form, detect that and then perform login using the role provided (or default mapping). Detect login forms by presence of common selectors (e.g., `input[type="text"]`, `input[name*=user]`, `input[type="password"]`, `form[action*="login"]`).
- If the environment always lands on a student-login landing page, the agent should still call `browser_navigate(<target-url>)` first. If a redirect occurs, sign in as the requested role and then re-navigate to the Target URL to confirm access.
- If already authenticated as a different user, prefer to sign out (if a sign-out link is present) then sign in using the requested role to get a clean session.

Notes: this pre-navigation step prevents the agent from always authenticating as `student` when the server redirects to a common login entrypoint. It also makes the Playwright run more deterministic for pages behind role-based gates.

2) **Strict Mobile Configuration & Navigation**
- Emulate **iPhone SE** (375×667 pixels). Force viewport constraints via `browser_evaluate`.
- **Strict Check (Viewport Meta):** Evaluate if the page has `<meta name="viewport" content="width=device-width, initial-scale=1">`. Flag as a critical failure if missing.
- Navigate to the target URL.

3) **Baseline Console & Network** - Capture initial console errors via `browser_console_messages`.

4) **Snapshot & Extract Interactive Elements** - Obtain an accessibility tree snapshot. 

5) **Strict Theme & Mobile Layout Evaluation (CSS Checks)**
- Use `browser_evaluate` to check for theme consistency AND mobile usability:

    () => {
      const issues = [];
      // 1. Horizontal Overflow
      if (document.documentElement.scrollWidth > window.innerWidth) issues.push("Horizontal scrolling detected (Broken Mobile Viewport).");
      
      // 2. Strict Tap Target Size (Apple/Google minimum is 44x44px for touch)
      const buttons = document.querySelectorAll('button, a, input[type="submit"]');
      buttons.forEach(b => {
        const rect = b.getBoundingClientRect();
        if (rect.width > 0 && (rect.width < 44 || rect.height < 44)) {
          issues.push(`Tap target too small: ${b.innerText || b.id} is ${rect.width}x${rect.height}px.`);
        }
      });
      return issues;
    }

6) **Interact & Fuzz (Sequential Testing)** - For each interactive element:
  a) **Buttons/Links:** Click via `browser_click`, wait 1000ms.
  b) **Inputs & Security Testing (If Fuzzing is ON):**
  - **XSS Test:** Inject `<img src="x" onerror="console.error('CRITICAL_XSS_VULNERABILITY_FOUND')">`. 
  - **SQLi Test:** Inject `' OR '1'='1`. 

7) **Detect Failures & Breaches** - Scan `browser_console_messages` and network logs explicitly for:
  - `CRITICAL_XSS_VULNERABILITY_FOUND`
  - SQL syntax traces or 500 Internal Server errors.

8) **Aggregate Report & Present Results** - Deliver a concise summary categorizing: Security Threats (XSS/SQLi), Strict Mobile Issues (Overflow, tap targets), Theme Inconsistencies, and Functional Bugs.

9) **Cleanup & Session Reset**
- Clear `localStorage`, `sessionStorage`, and cookies via `browser_evaluate`, then reload to leave the page clean.

## STOP COMMAND
When cleanup is complete, output exactly:
WAITING_FOR_HUMAN_OK