---
name: "image-to-component"
description: "Convert UI screenshots to Bootstrap 4 components."


---

# Skill 15: Image to Component

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

##  Vision AI MCP Analysis
Extract from screenshots:
1. Layout structure (grid, flexbox)
2. Components (cards, buttons, inputs)
3. Color palette (hex codes)
4. Responsive behavior

##  Output Requirements
- Bootstrap 4 HTML
- SCSS with variables (no hardcoded colors)
- 320px compatible

##  MCP Integration
- **Playwright MCP:** Analyze image, extract colors.
- **Filesystem MCP:** Save components to `resources/assets/sass/components/`.

##  Escalation Protocol
- If component structure unclear, STOP and output:
` ESCALATION REQUIRED. Component extraction unclear: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
