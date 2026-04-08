---
name: "playwright-chaos-tester"
description: "Aggressive stress-testing tool. Simulates auto-clickers, rapid reloads, and form-submission spam to test rate-limiting (429s), race conditions, and duplicate-prevention for Save/Submit buttons."
---

# Playwright Chaos & Stress Tester

## Purpose
Use the Playwright MCP server to aggressively test a web page's resilience against malicious user behavior, specifically focusing on button spamming, rapid reload-and-click attacks, and missing rate limits. 

The highest-priority verification is duplicate-prevention for primary actions such as Save, Submit, Next, Approve, and Update.

**WARNING: This is an aggressive testing routine. Ensure the target server is a staging or local environment.**

## Required MCP Tools
- `browser_navigate`
- `browser_console_messages`
- `browser_evaluate`
- `browser_click`
- `browser_wait_for`

## Workflow (Steps 1–7)

1) **Get Inputs**
- Prompt the user for: Target URL and Target Element (e.g., "Submit button", "Like button", "Login form"). If none provided, auto-detect primary action buttons.

### Target Button Priority (for duplicate-prevention checks)
- `button[type="submit"]`
- Buttons whose text contains: `save`, `submit`, `next`, `approve`, `confirm`, `update`, `send`
- Inputs with `type="submit"`

2) **Baseline & Navigation**
- Navigate to the Target URL. Clear previous `browser_console_messages` to ensure clean error logs.

3) **Phase 1: The Auto-Clicker Spam (Race Condition Test)**
- Identify the target button.
- Use `browser_evaluate` to inject a script that clicks the button extremely fast (e.g., 20 times in 1 second) to test if the backend processes duplicate requests.

    (selector) => {
      const btn = document.querySelector(selector);
      if (!btn) return 'Button not found';
      for (let i = 0; i < 20; i++) {
        setTimeout(() => btn.click(), i * 50); // Click every 50ms
      }
      return 'Spam execution complete';
    }

- Wait 2000ms. Check if the UI reflects 20 actions, or if the server returned `429 Too Many Requests`.

### Required Assertions for Save/Submit Buttons
- Verify the button becomes disabled (or logically blocked) immediately after first click.
- Verify at most one successful request is accepted for the same action during the spam window.
- Verify duplicate attempts are blocked by UI debouncing or backend protection (`409`, `422`, `429`, or equivalent guarded response).
- Verify no duplicate records/side effects are created.

4) **Phase 2: The Reload-and-Fire Attack**
- Execute `browser_navigate` to reload the page.
- IMMEDIATELY execute a `browser_evaluate` script to find the button and force a click event before the page reaches the 'networkidle' state (bypassing JS-based validation).
- Wait 2000ms. Check if the premature click successfully bypassed frontend guards.

5) **Phase 3: Concurrent Form Submission (If Applicable)**
- If testing a form, fill out the fields.
- Use `browser_evaluate` to trigger the form's `submit()` event 10 times simultaneously in a tight loop.

    (formSelector) => {
      const form = document.querySelector(formSelector);
      if (!form) return;
      for(let i=0; i<10; i++) {
         form.submit(); // Bypass button clicks, hit the form directly
      }
    }

6) **Aggressive Failure Detection**
- Analyze network responses and `browser_console_messages`:
  - **PASS:** The UI blocks duplicate clicks and/or the server blocks duplicate submissions (`409`, `422`, `429`, or idempotent single-write behavior).
  - **CRITICAL FAIL (Race Condition):** The server processes multiple duplicate requests.
  - **CRITICAL FAIL (Crash):** The server returns `500 Internal Server Error`.
  - **CRITICAL FAIL (Duplicate Side Effect):** Multiple records or repeated state transitions are created from one user intent.

7) **Chaos Report & Cleanup**
- Generate a "Chaos Report" detailing exactly how many clicks were spammed, how many requests hit the network, and how the server responded.
- Include a dedicated section: `Duplicate Prevention Verdict` with:
  - target button name
  - disabled-state observed (yes/no)
  - accepted writes count
  - blocked duplicates count
  - final pass/fail
- Clear `localStorage`, `sessionStorage`, and cookies, then reload.

## STOP COMMAND
When cleanup is complete, output exactly:
WAITING_FOR_HUMAN_OK