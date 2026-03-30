---
name: "ui-generator"
description: "Bootstrap 4 UI generation from designs/screenshots."
applyTo:
  - "resources/views/*"
  - "resources/assets/sass/*"
version: "1.0-legacy"

---

# Skill 07: UI Generator

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

##  STRICT RESPONSIVENESS
Must work on:
- iPhone SE: 320px
- iPad: 768px
- Desktop: 1920px

##  Bootstrap 4 Standards
```html
<!-- Cards -->
<div class="card shadow-sm border-0">

<!-- Buttons -->
<button class="btn btn-primary rounded-pill">

<!-- Grid -->
<div class="container">
  <div class="row">
    <div class="col-12 col-md-6 col-lg-4">
    </div>
  </div>
</div>
```

##  NO Hardcoded Pixel Widths
```css
/*  WRONG */
width: 500px;

/*  CORRECT */
width: 100%;
max-width: 500px;
```

##  MCP Integration
- **Filesystem MCP:** Verify SCSS files in `resources/assets/sass/`.
- **Playwright MCP:** Test responsiveness at 320px, 768px, 1920px.

##  Escalation Protocol
- If Bootstrap 5 classes detected (e.g., `ms-auto`), STOP and output:
` ESCALATION REQUIRED. Bootstrap 5 detected. Use Bootstrap 4 (ml-auto/mr-auto).`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
