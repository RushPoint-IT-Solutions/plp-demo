---
name: "playwright-debugger"
description: "Automatically interact with a web page, check mobile responsiveness (iPhone SE), click buttons, fill forms, and report console errors and network failures."
---

# Playwright Debugger

## Purpose

Use the Playwright MCP server to explore a web page, interact with controls, verify mobile responsiveness (specifically iPhone SE), capture JavaScript console errors and network failures, and produce a structured, actionable report.

## Known Test Credentials & Routing

When testing login portals, use the following pre-configured test credentials based on the target role. **Do not ask the user for test credentials if these roles apply:**

- **Registrar** (Target URL: `http://localhost:8000/login/registrar`): Username `admin` / Password `password`
- **Student**: Username `student` / Password `student`
- **Faculty**: Username `faculty` / Password `faculty`
- **Applicant**: Username `2526B0177` / Password `PLP-2526B0177`

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
- Confirm whether autofill is allowed and whether additional test credentials will be provided beyond the pre-configured ones above (never use real credentials).

## Workflow (steps 1–9)

1) Get inputs  
- Prompt the user for:
  - Target URL (if not starting at a known URL like the Registrar link).
  - Allow destructive actions? (yes/no) — default: no.
  - Allow autofill? (yes/no).
  - Emulate Mobile View (iPhone SE)? (yes/no) — default: yes.

2) Configure Viewport & Navigate (iPhone SE Emulation)
- If mobile view is enabled, properly emulate **iPhone SE** device context with:
  - **Viewport:** 375×667 pixels
  - **Device Pixel Ratio:** 2
  - **User Agent:** iPhone mobile agent (Safari on iOS)
  - **Touch:** Enabled
  - **Viewport meta tag:** Assume `<meta name="viewport" content="width=device-width, initial-scale=1">`
- **IMPORTANT:** Do NOT just resize the viewport. Use proper device emulation or use `browser_evaluate` to inject CSS forcing exact viewport bounds:
  ```javascript
  // Force iPhone SE viewport and hide sidebars/elements not visible at 375px
  document.documentElement.style.width = '375px';
  document.documentElement.style.maxWidth = '375px';
  document.body.style.width = '375px';
  document.body.style.maxWidth = '375px';
  document.body.style.overflow = 'auto';
  // If sidebar exists, hide it on mobile
  var sidebar = document.querySelector('.plp-sidebar, .sidebar, nav[class*=side]');
  if (sidebar && window.innerWidth <= 600) {
    sidebar.style.display = 'none';
  }
  ```
- Navigate to the target URL and record navigation errors.

    ```json
    {
      "tool": "browser_navigate",
      "params": { "url": "<URL>", "waitUntil": "networkidle" }
    }
    ```

- Capture and record any navigation error response.
- **After navigation,** inject the viewport constraint via `browser_evaluate` to properly simulate iPhone SE without cropping.

3) Baseline console messages  
- Capture initial console warnings/errors to create a baseline.

    ```json
    {
      "tool": "browser_console_messages",
      "params": { "level": "warning", "all": true }
    }
    ```

- Save the result as `baselineConsole`.

4) Snapshot & extract interactive elements (Mobile Context)  
- Obtain a snapshot/accessibility tree and extract interactive elements. 
- **Mobile Check:** Actively look for mobile-specific navigation elements (e.g., "hamburger" menus, off-canvas toggles) that appear in the iPhone SE view. Record `ref`, `role`, and `accessibleName` for each element.

    ```json
    {
      "tool": "browser_snapshot",
      "params": {}
    }
    ```

- Use `ref` identifiers returned by the snapshot for stable interaction targets.

5) Interact with elements (sequential)  
- For each interactive element (prioritize mobile menus, then buttons/links, then inputs):

  a) Buttons/links:
  - If `accessibleName` contains destructive keywords (delete, remove, destroy, pay, purchase, submit, logout, sign out), ask user permission before clicking.
  - Otherwise:
    1. Collect `preActionConsole`.
    2. Click the element via `browser_click`.
    3. Wait for a short period via `browser_wait_for` (2000ms).
    4. Collect `postActionConsole`.
    5. Compute `newMessages = postActionConsole - preActionConsole` and attach to the per-element report.

  b) Inputs (textbox/select/textarea):
  - If autofill allowed, prioritize the "Known Test Credentials" provided above if a login form is detected. Otherwise, use safe sample values (email: `test@example.com`, text: `test`, phone: `09171234567`).
  - Type into the element via `browser_type`.
  - Do not submit forms unless explicitly permitted; if permitted, handle submission as a button click.

6) Detect network failures  
- Scan console messages and collected data for network issues (e.g., `Failed to load resource`, `net::ERR_`, HTTP status codes `404`, `500`, timeouts, fetch/XHR errors).  
- Optionally gather network-related performance entries via `browser_evaluate`.

7) Evaluate Mobile Layout  
- Run a quick evaluation to check for horizontal scrolling or elements breaking the iPhone SE viewport width:

    ```json
    {
      "tool": "browser_evaluate",
      "params": {
        "function": "() => document.documentElement.scrollWidth > window.innerWidth"
      }
    }
    ```

- Flag as a responsive design error if the result is `true`.

8) Aggregate report  
- Build a structured report containing:
  - Target URL and timestamp
  - Emulation Status (e.g., iPhone SE)
  - Layout / Viewport Overflow Issues
  - `navigationErrors` (if any)
  - `consoleBaseline` (initial warnings/errors)
  - Per-element results: `{ accessibleName, role, ref, actionTaken, newConsoleMessages }`
  - `networkFailures` (list)
  - Summary (counts and highest-severity items first)

9) Present results  
- Present a concise, human-readable summary grouped by severity and action.  
- Explicitly note any UI elements that were hidden or broken in the iPhone SE view.
- Include raw console messages and stack traces when available.  
- Include the structured report payload as machine-readable JSON where useful.

## Safety limits
  
- Never enter real credentials; explicitly rely on the predefined test credentials listed at the top.  
- Ask before performing destructive actions (form submits, purchases, deletes).  
- Abort and notify the user if the page navigates off-domain or shows strong anti-automation behavior (CAPTCHA, bot-detection interstitials).

## STOP COMMAND

When the skill completes and the report is delivered, output exactly:

WAITING_FOR_HUMAN_OK