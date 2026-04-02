---
name: "form-creator"
description: "Convert form screenshots to Laravel Blade forms with a fixed, non-responsive 'zoom-out' A4 layout."

---

# Skill 14: Form Creator

## 🚨 LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

## 📐 Layout Constraints (Fixed "Zoom-Out" A4 Print Style)
- **CRITICAL:** Forms must be STRICTLY FIXED-WIDTH and NON-RESPONSIVE.
- **1:1 Screenshot Match:** Copy the layout from the provided screenshot exactly. The HTML layout must be visually identical to the physical print view. 
- **No Responsive Classes:** Do NOT use Bootstrap's responsive grid classes (e.g., `col-sm-*`, `col-md-*`, `col-lg-*`). Use fixed columns (e.g., `col-6`) or exact pixel widths to enforce placement.
- **A4 Document Wrapper with Margins:** Wrap the entire form in a fixed container that mimics an A4 paper with a 2-inch top margin and 1-inch margins on all other sides. Example wrapper: 
  `<div style="width: 794px; min-height: 1123px; margin: 0 auto; background: white; padding: 2in 1in 1in 1in; color: #000; font-size: 10pt; box-sizing: border-box;">`
- **Typography Overrides:** Ensure ALL text, labels, and inputs use `font-size: 10pt !important;` and `color: #000 !important;`. You must override Bootstrap's default gray text colors.
- **No Stacking:** Elements must stay exactly where they are placed horizontally and must never collapse or stack vertically on smaller screens. 
- **Goal:** The output must behave exactly like a printed A4 document that forces the browser to "zoom out" on smaller screens.

## 🎨 Field Styling & Interactions
- **Editable Fields:** All form fields must be functional and editable (e.g., actual `<input>`, `<textarea>`, `<select>` tags), allowing the user to fill them out.
- **Custom Checkboxes:** Do NOT use the default browser checkbox UI. Checkboxes must be styled or structured to look exactly like `{ }` in the print view while still functioning as an editable/clickable input.

## 🚫 Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

## ⚙️ Workflow
- Screenshot ➡️ AI extracts fields from the provided screenshot.
- Generate a Laravel Blade form and corresponding `FormRequest` for validation.
- Insert `@csrf` and proper `old()` bindings for each field.
- Write files to `resources/views/forms/` and `app/Http/Requests/`.

## 📋 Field Type Mapping
| Form Element | Blade Input Type |
|--------------|------------------|
| Text box | `text` |
| Long text | `textarea` |
| Email | `email` |
| Password | `password` |
| Dropdown | `select` |
| Date picker | `date` |
| File upload | `file` |

## 🔒 Security (AUTO-INCLUDED)
- CSRF token (`@csrf`)
- Validation rules (FormRequest)
- XSS protection (`{{ }}`)

## 🤖 MCP Integration
- **Playwright MCP:** Analyze form screenshot.
- **Filesystem MCP:** Verify form files created.

## ⚠️ Escalation Protocol
- If form fields misidentified, STOP and output:
`🚨 ESCALATION REQUIRED. Form field mapping error: <detail>.`

## 🛑 STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.