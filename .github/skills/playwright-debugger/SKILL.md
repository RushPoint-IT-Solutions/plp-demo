---
name: "playwright-debugger"
description: "Automatically interact with a web page, click buttons, fill forms, and report console errors and network failures."
---

# Playwright Debugger

## Purpose

Use the Playwright MCP server to explore a web page, interact with controls, capture JavaScript console errors and network failures, and produce a structured, actionable report.

## Required MCP Tools (use only these)

- `browser_navigate`
- `browser_console_messages`
- `browser_snapshot`
- `browser_click`
- `browser_type`
- `browser_evaluate`
- `browser_wait_for`

## Preconditions

- The Playwright MCP server is reachable.
- For live visual debugging start the server in headed mode (`--headless=false`).
- Confirm the target URL and whether destructive actions (form submits, deletes, purchases) are permitted. Default: do NOT perform destructive actions.
- Confirm whether autofill is allowed and whether test credentials will be provided (never use real credentials).

## Workflow (steps 1–8)

1) Get inputs  
- Prompt the user for:
  - Target URL.
  - Allow destructive actions? (yes/no) — default: no.
  - Allow autofill? (yes/no).

2) Navigate  
- Navigate to the target URL and record navigation errors.

```json
{
  "tool": "browser_navigate",
  "params": { "url": "<URL>", "waitUntil": "networkidle" }
}
```

- Capture and record any navigation error response.

3) Baseline console messages  
- Capture initial console warnings/errors to create a baseline.

```json
{
  "tool": "browser_console_messages",
  "params": { "level": "warning", "all": true }
}
```

- Save the result as `baselineConsole`.

4) Snapshot & extract interactive elements  
- Obtain a snapshot/accessibility tree and extract interactive elements (buttons, links, inputs). Record `ref`, `role`, and `accessibleName` for each element.

```json
{
  "tool": "browser_snapshot",
  "params": {}
}
```

- Use `ref` identifiers returned by the snapshot for stable interaction targets.

5) Interact with elements (sequential)  
- For each interactive element (prioritize buttons/links then inputs):

  a) Buttons/links:
  - If `accessibleName` contains destructive keywords (delete, remove, destroy, pay, purchase, submit, logout, sign out), ask user permission before clicking.
  - Otherwise:
    1. Collect `preActionConsole`:

    ```json
    {
      "tool": "browser_console_messages",
      "params": { "level": "warning", "all": true }
    }
    ```

    2. Click the element:

    ```json
    {
      "tool": "browser_click",
      "params": { "ref": "<ref>" }
    }
    ```

    3. Wait for a short period:

    ```json
    {
      "tool": "browser_wait_for",
      "params": { "time": 2000 }
    }
    ```

    4. Collect `postActionConsole`:

    ```json
    {
      "tool": "browser_console_messages",
      "params": { "level": "warning", "all": true }
    }
    ```

    5. Compute `newMessages = postActionConsole - preActionConsole` and attach to the per-element report.

  b) Inputs (textbox/select/textarea):
  - If autofill allowed, use safe sample values (email: `test@example.com`, text: `test`, phone: `09171234567`).
  - Type into the element:

  ```json
  {
    "tool": "browser_type",
    "params": { "ref": "<ref>", "text": "<sample>" }
  }
  ```

  - Do not submit forms unless the user explicitly permits submission; if permitted, handle submission as a button click (follow the button flow above).

6) Detect network failures  
- Scan console messages and collected data for network issues (e.g., `Failed to load resource`, `net::ERR_`, HTTP status codes `404`, `500`, `502`, `503`, `504`, timeouts, fetch/XHR errors).  
- Optionally gather network-related performance entries via `browser_evaluate`:

```json
{
  "tool": "browser_evaluate",
  "params": {
    "function": "() => performance.getEntries().filter(e => e.initiatorType === 'fetch' || e.initiatorType === 'xmlhttprequest').map(e => ({ name: e.name, initiator: e.initiatorType, duration: e.duration, transferSize: e.transferSize }))"
  }
}
```

- Record suspicious entries as possible network failures.

7) Aggregate report  
- Build a structured report containing:
  - Target URL and timestamp
  - `navigationErrors` (if any)
  - `consoleBaseline` (initial warnings/errors)
  - Per-element results: `{ accessibleName, role, ref, actionTaken, newConsoleMessages }`
  - `networkFailures` (list)
  - Summary (counts and highest-severity items first)

8) Present results  
- Present a concise, human-readable summary grouped by severity and action.  
- Include raw console messages and stack traces when available.  
- Offer remediation notes where clear (e.g., JS error stack locations, failing resource URLs).  
- Include the structured report payload as machine-readable JSON where useful.

## Safety limits

- Default maximum automated clicks: 25 per run (configurable by user).  
- Never enter real credentials; request explicit test credentials if needed.  
- Ask before performing destructive actions (form submits, purchases, deletes).  
- Abort and notify the user if the page navigates off-domain or shows strong anti-automation behavior (CAPTCHA, bot-detection interstitials).

## STOP COMMAND

When the skill completes and the report is delivered, output exactly:

```
WAITING_FOR_HUMAN_OK
```
