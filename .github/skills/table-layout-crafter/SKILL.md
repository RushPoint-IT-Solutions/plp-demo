---
name: "table-layout-crafter"
description: "Create responsive Laravel Blade tables using Slot Monitoring-style formatting: centered columns, content-fit widths, mobile-first overflow handling, print zoom-out tuning, and Playwright-browser validation. Use when building or refactoring any registrar/student data table."
---

# Table Layout Crafter

## Purpose
Build reusable, responsive tables that match the Slot Monitoring visual format while keeping columns content-fit and centered.

## Stack Context
- Laravel 5.7 Blade views
- Bootstrap 4 wrappers allowed
- SCSS in `resources/sass/`
- Compiled with Laravel Mix (`npm run dev`)

## Non-Negotiable Rules
- Do not set fixed per-column widths (`th:nth-child(...) { width: ... }`).
- Do not force `table-layout: fixed` for data tables.
- Keep all table columns center-aligned by default.
- Use content-fit sizing with horizontal overflow wrapper on small screens.
- On mobile, keep page gutters minimal; use only the smallest practical margin/padding so the table can use the limited screen width.
- Keep CSS in SCSS files only; no inline styles in Blade.
- Validate responsiveness using the external Playwright Chromium browser session, not VS Code preview screenshots alone.

## Allowed Exception
- For fixed-grid schedule tables (example: faculty assignment plotted weekly grid), fixed uniform columns are allowed:
  - `table-layout: fixed;`
  - equal-width day columns (1/7 each)
  - keep other data tables on content-fit mode.

## Canonical Blade Pattern
Use this wrapper and class format for data tables:

```blade
<div class="student-table-wrapper table-responsive my-table-wrap">
    <table class="student-table registrar-table my-table">
        <thead>
            <tr>
                <th>Header</th>
                <th>Header</th>
                <th>Header</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Value</td>
                <td>Value</td>
                <td>Value</td>
            </tr>
        </tbody>
    </table>
</div>
```

## Canonical SCSS Pattern

```scss
.my-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.my-table {
  width: max-content;
  min-width: 100%;
  table-layout: auto;
  border-collapse: separate;
  border-spacing: 0;
}

.my-table th,
.my-table td {
  text-align: center;
  vertical-align: middle;
  white-space: normal;
  word-break: break-word;
  overflow-wrap: anywhere;
}
```

## Print Zoom-Out Pattern
Use this when print tables are too dense or clipped:

```scss
@media print {
  .my-print-sheet {
    padding: 0.55in 0.22in 0.08in 0.22in !important;
    zoom: 0.92 !important;
    transform-origin: top left !important;
  }

  .my-print-sheet .my-table th,
  .my-print-sheet .my-table td {
    font-size: 7.1pt !important;
    padding: 2px 3px !important;
  }
}
```

## Optional Content Rules
- Numeric cells: keep center-aligned and optionally bold.
- Long text cells: allow wrapping; avoid hard truncation unless explicitly requested.
- Status/summary rows: keep centered unless there is a strict reporting requirement.

## Responsive Validation Matrix
Always validate before completion:
- 320x568 (small phones)
- 375x667 (iPhone SE viewport target)
- 390x844 (modern phone)
- 768x1024 (tablet)
- 1366x768 (laptop)
- 1920x1080 (desktop)

Execution requirement:
- Run checks using Playwright Chromium automation via CDP connection (`chromium.connectOverCDP`).
- Capture outputs from Playwright-run layout metrics.
- Do not mark done based only on VS Code embedded preview behavior.

## Conditional Overflow Rule
Do not leave horizontal scrolling enabled by default when the table already fits.

- Keep the wrapper non-scrollable unless a browser measurement shows the table is wider than its container.
- If the table fits, the wrapper must stay `overflow-x: hidden` or equivalent.
- Only switch the wrapper to `overflow-x: auto` after confirming real overflow in Playwright.

## Playwright Debug Loop (Required)
Use this loop when a table still looks oversized or clipped. 

**CRITICAL:** Do NOT attempt to launch a new Playwright browser instance (`chromium.launch()`). The Copilot sandbox will close it and fail. You MUST connect to the user's externally running Chrome instance via CDP on port 9222.

1. Connect to the external browser using `chromium.connectOverCDP('http://localhost:9222')`.
2. Get the default context and open a new page.
3. Set viewport to one matrix size.
4. Capture layout metrics via `page.evaluate()`.
5. Close the *page* when done, but do NOT close the *browser* connection.
6. Patch SCSS/Blade only where needed based on metrics.
7. Rebuild assets and repeat until checks pass.

Minimum metrics to capture at each viewport:

```js
() => {
  const table = document.querySelector('.my-table');
  return {
    viewportWidth: window.innerWidth,
    docScrollWidth: document.documentElement.scrollWidth,
    hasHorizontalOverflow: document.documentElement.scrollWidth > window.innerWidth,
    tableWidth: table ? table.getBoundingClientRect().width : null,
    tableScrollWidth: table ? table.scrollWidth : null,
  };
}
```

Pass criteria per viewport:
- No clipped headers/cells.
- Table remains readable and center-aligned.
- If horizontal scrolling is expected, it must be intentional inside the table wrapper only.
- No page-level overflow caused by table layout.
- Mobile view should be visibly zoomed-out/compacted when required by design, with tight outer spacing instead of large margins.

Validation checks:
- No clipped headers or cells
- Horizontal scroll works on narrow screens
- Columns remain content-fit (no fixed width artifacts)
- Column text remains centered
- Horizontal scrolling is disabled when the table fits within the viewport/container

## Refactor Checklist
1. Remove fixed width selectors from existing table SCSS.
2. Remove fixed-layout table mode where used for standard data tables.
3. Add content-fit width pair (`width: max-content; min-width: 100%`).
4. Ensure wrapper only gets `overflow-x: auto` when actual overflow exists.
5. Confirm center alignment in `th` and `td`.
6. Keep mobile gutters/padding minimal; do not add desktop-sized margins on small screens.
7. Rebuild assets and retest viewport matrix.
8. Run Playwright Chromium validation and record findings.

## STOP COMMAND
WAITING_FOR_HUMAN_OK

```json
{
  "code": "const { chromium } = require('playwright');\n(async () => {\n  const browser = await chromium.connectOverCDP('http://localhost:9222');\n  const context = browser.contexts()[0];\n  const page = await context.newPage();\n  await page.goto('[http://127.0.0.1:8000/registrar/registrar-menu/scheduling/slot-monitoring/reports/closed](http://127.0.0.1:8000/registrar/registrar-menu/scheduling/slot-monitoring/reports/closed)', { waitUntil: 'domcontentloaded' });\n  await page.setViewportSize({ width: 375, height: 667 });\n  const metrics = await page.evaluate(() => ({\n    title: document.title,\n    width: window.innerWidth,\n    scrollWidth: document.documentElement.scrollWidth,\n    overflow: document.documentElement.scrollWidth > window.innerWidth,\n    wrapScrollClass: document.querySelector('.sm-report-table-wrap') ? document.querySelector('.sm-report-table-wrap').classList.contains('is-scrollable') : null,\n    wrapOverflowX: document.querySelector('.sm-report-table-wrap') ? getComputedStyle(document.querySelector('.sm-report-table-wrap')).overflowX : null,\n  }));\n  await page.close();\n  return metrics;\n})();"
}
```