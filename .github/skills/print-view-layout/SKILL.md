---
name: "print-view-layout"
description: "Build print-only layouts for Registrar pages: hide UI chrome/actions and show only document content such as identity headers, records tables, and detail blocks."
---

# Print View Layout

## Purpose
Create clean, predictable print outputs for Laravel Blade pages by separating on-screen controls from print content.

## Rules
- Keep all print styling in Sass/SCSS files. Never add inline print styles in Blade.
- Use @media print to hide navigation/chrome:
  - Sidebar, topbar, page title bars, footer, toasts, modal overlays, action buttons, dropdown menus.
- Keep only print-content blocks visible:
  - Identity banners, record/certificate tables, and narrative detail sections.
- Remove interactive-only columns in print (Action, kebab menus, buttons).
- Ensure print tables fit page width by removing fixed min-width constraints in print mode.
- If a page has per-record detail data, print every record's full detail card (not only the selected row).
- Prefer compact column layouts inside each record card (grid fields + side-by-side description/remarks) to save page space.
- Avoid duplicate identity blocks in print. If a dedicated print sheet already shows Student ID/Name, hide the page's top identity banner in print.
- Preserve accessibility and semantics in normal view; do not break non-print interaction.

## Implementation Checklist
1. Identify page containers that should print.
2. Add scoped print rules using body page class (example: body.page-student-discipline).
3. Hide interactive elements and non-content chrome.
4. Force content wrappers to full-width and zero extra margins/padding.
5. Mark large detail cards with page-break-inside: avoid when needed.
6. For record-heavy pages, generate a dedicated print sheet container and populate it from current page data before printing.
7. Verify print preview shows only relevant content.

## Student Discipline Pattern
- Print output must include student identity (Student ID + Student Name) and every conduct record.
- Keep exactly one student identity section in print output.
- Hide the interactive conduct table and selected-row card in print mode.
- Hide the on-screen top student banner in print when the print sheet header already contains student identity.
- Render a stacked list of detail cards where each card uses:
  - 4-column metadata grid (Incident Date, Action Date, Case Type, Action Type, Called By, Counselor, Walk In, Updated By)
  - 2-column narrative grid (Description, Remarks)

## Stop Command
WAITING_FOR_HUMAN_OK
