---
name: "form-creator"
description: "Convert form screenshots to Laravel Blade forms with a fixed, non-responsive 'zoom-out' A4 layout and fluid sentence-style inputs."
---

# Skill 14: Form Creator

## 🚨 LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

## 📐 Layout Constraints (Fixed "Zoom-Out" A4 Print Style)
- **CRITICAL:** Forms must be STRICTLY FIXED-WIDTH (210mm) and NON-RESPONSIVE.
- **1:1 Screenshot Match:** The HTML layout must be visually identical to the physical print view provided in the screenshot.
- **Fluid Inline Fields (The "Sentence" Rule):** For fields that appear inside a sentence (e.g., "I, ________, a student..."), do NOT use fixed widths. Use `display: flex` or `display: inline-flex` for the wrapper. The input should have `border: none; border-bottom: 1px solid black; flex-grow: 1;` so the line length adjusts dynamically to the text while filling the gap.
- **No Responsive Classes:** Do NOT use Bootstrap's responsive grid classes (e.g., `col-sm-*`, `col-md-*`). Use fixed columns (e.g., `col-6`) or exact pixel widths to enforce placement.
- **A4 Document Wrapper:** Wrap the entire form in `<div class="a4-wrapper">`.
  - **Screen Style:** `width: 210mm; min-height: 297mm; margin: 0 auto; background: white; padding: 1in; color: #000; font-size: 10pt; box-sizing: border-box; transform-origin: top center;`
  - **Print Style (CSS @media print):** Force `padding: 2in 0.5in 0 0.5in !important;`. This MUST overwrite the screen padding to ensure the top margin is exactly 2 inches on paper, with half-inch sides and 0 bottom margin.
  - **Print Font Guard:** In `@media print`, force `.a4-wrapper, .a4-wrapper * { font-size: 8pt !important; line-height: 1.2 !important; }` so output is readable and consistent.
  - **Print Scale Guard (Critical):** If screen view uses `zoom` or `transform` for viewport fit, reset print with `.a4-wrapper { zoom: 1 !important; transform: none !important; }`.
  - **Global Print Override Guard:** Neutralize inherited framework print shrink rules (e.g., `body { min-width: 992px !important; }`) with `html, body, .container, .container-fluid { width: 100% !important; min-width: 0 !important; max-width: none !important; }` in `@media print`.
- **Anti-Cropping & Screen Fit:** The form must NEVER be cropped horizontally. Ensure the 210mm width is ALWAYS fully visible. If the screen is smaller than 794px, use CSS `transform: scale()` or `zoom` to scale the entire `.a4-wrapper` down to fit the viewport.
- **Zoom Reset Rule:** Any screen-only zoom/scale used for fit MUST be explicitly reset inside `@media print` to avoid tiny print output.
- **No Stacking:** Elements must stay exactly where they are placed horizontally and must never collapse or stack vertically on mobile/small screens.

## 🎨 Typography, Colors & Borders
- **Strict Black & White:** Everything must be strictly `#000` (black) for text, borders, and dividers unless a color is explicitly requested.
- **Font Rules (Screen):** Use `font-size: 10pt !important;` for all text, labels, and inputs in normal screen view.
- **Font Rules (Print):** Use `font-size: 8pt !important;` for all text, labels, and inputs inside `@media print`.
- **Border Strictness:** Apply `border-color: #000 !important;` to all inputs and tables to override Bootstrap's default gray.
- **Print View Border Removal:** Inside `@media print`, all form inputs (`input`, `textarea`, `select`) must have `border: none !important;` EXCEPT for the `border-bottom` used for fill-in-the-blank lines.

## 🛠️ Field Styling & Interactions
- **Editable Fields:** All fields must be functional and editable `<input>`, `<textarea>`, or `<select>` tags.
- **Auto-Expanding Table Cells:** For fields inside grid/table cells where text might wrap, do NOT use standard `<input>` tags. Use `<textarea rows="1">` with CSS `resize: none; overflow: hidden; height: auto;`. Apply JavaScript (inline or external) to auto-expand the vertical height based on content length so text is NEVER hidden. Example: `oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"`
- **Custom Checkboxes:** Do NOT use default browser checkboxes. Style them to look exactly like `{ }` (brackets) in the print view while remaining clickable on the screen.

## 🎛️ Conditional Features (ONLY IF EXPLICITLY REQUESTED)
- **Print Button:** If asked, add a 'Print' button styled with Bootstrap `btn-success` (green). Use `window.print()`. Apply `d-print-none` to hide it during printing.
- **Search/Auto-Fill:** If asked, add a search input/button at the very top (outside the A4 wrapper) for student data lookups. Apply `d-print-none` to hide it during printing.

## 🚫 Explicitly Forbidden PHP 8+ Features
- NO `match` expressions, Union types (`string|int`), Nullsafe operator (`?->`), Named arguments, Constructor property promotion, Arrow functions (`fn() =>`), Typed properties, or Null-coalescing assignment (`??=`).

## ⚙️ Workflow & Security
- **Workflow:** Screenshot ➡️ Extract Fields ➡️ Generate Blade Form + `FormRequest` ➡️ Write to `resources/views/forms/` and `app/Http/Requests/`.
- **Security:** Always include `@csrf`, `old()` bindings for every field, and XSS protection `{{ }}`.
- **MCP Integration:** Use Playwright MCP to analyze and Filesystem MCP to verify file creation.

## ⚠️ Escalation & Stop
- If fields are misidentified: `🚨 ESCALATION REQUIRED. Form field mapping error: <detail>.`
- Output `WAITING_FOR_HUMAN_OK` when the file is generated.