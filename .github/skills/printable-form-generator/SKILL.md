---
name: "printable-form-generator"
description: "Print-ready HTML/CSS for forms and certificates."
applyTo:
  - "resources/views/printables/*"
  - "resources/assets/sass/print.scss"
version: "1.0-legacy"

---

# Skill 10: Printable Form Generator

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Extract from Form Images
| Element | Specification |
|---------|---------------|
| Paper Size | A4 (210mm  297mm) or Letter (8.5"  11") |
| Orientation | Portrait or Landscape |
| Margins | In mm (not px): 25mm top, 20mm sides |
| Font Sizes | Title: 24pt, Body: 10pt, Footer: 9pt |

##  Print-CSS Requirements
```css
@page {
    size: A4;
    margin: 25mm 20mm;
}

@media print {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    page-break-inside: avoid;
}
```

##  NO Hardcoded Pixel Widths (Print)
Use metric units for print layout. Prefer `mm`/`cm` and relative sizing for elements.
```css
/*  WRONG for print */
width: 500px;

/*  CORRECT for print */
width: 100%;
max-width: 180mm;
```

##  MCP Integration
- **Filesystem MCP:** Verify `resources/assets/sass/print.scss` and compiled CSS are present.
- **Playwright MCP:** Capture print preview at A4/Letter and compare screenshots.
- **PDF MCP:** Render to PDF and validate page breaks and margins.

##  Escalation Protocol
- If margins are specified in `px`, page breaks occur mid-content, or fonts render incorrectly in print preview, STOP and output:
  ` ESCALATION REQUIRED. Print layout issue: <detail>. Architect review needed.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.

