---
name: "page-validator"
description: "Verify every page fully working before task complete."
applyTo:
  - "resources/views/*"
  - "app/Http/Controllers/*"
version: "1.0-legacy"

---

# Skill 21: Page Validator

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

##  Validation Checklist

### Visual Elements
 Text renders correctly
 Images load (no broken links)
 Colors match design

### Responsiveness Test
 320px (iPhone SE)
 768px (iPad)
 1920px (Desktop)

### Form Validation
 Required fields enforced
 Error messages display
 CSRF token present

### Performance
 Page loads under 2 seconds
 Under 20 database queries
 Zero console errors

##  MCP Integration
- **Playwright MCP:** Screenshot at 320px, 768px, 1920px.
- **MySQL MCP:** Check query count via Debugbar.

##  Escalation Protocol
- If any checklist item fails, STOP and output:
` ESCALATION REQUIRED. Page validation failed: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
