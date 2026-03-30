---
name: "figma-extractor"
description: "Extract colors, spacing, components from Figma to Bootstrap 4."
applyTo:
  - "resources/assets/sass/_variables.scss"
  - "resources/views/*"
version: "1.0-legacy"

---

# Skill 13: Figma Extractor

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

##  Figma Design Reference
- **Design:** [PLP-UERP](https://www.figma.com/design/uAZiZeLlrxm43JqGkJoyQB/PLP-UERP?node-id=0-1&p=f&t=cy4Ev1l9PDy2Tmms-0)
- **Access:** **Readonly (view only)**. Never attempt to edit or modify the Figma file.

##  Figma MCP Setup (Optional)
- The project primarily uses **Filesystem** and **Playwright** MCPs. A Figma MCP (`@modelcontextprotocol/server-figma`) can be installed with a personal access token (`FIGMA_TOKEN`). It is **not required**; extraction can also be done via Playwright screenshots.
- If installed, commands: `get_variable_defs`, `get_design_context`, `get_screenshot`.

##  Extract Design Tokens First
1. Colors  Save to `resources/assets/sass/_variables.scss`
2. Spacing  Save to `resources/assets/sass/_spacing.scss`
3. Typography  Save to `resources/assets/sass/_typography.scss`

##  Map Figma Components to Bootstrap 4
| Figma Component | Bootstrap 4 Class |
|-----------------|-------------------|
| Button Primary | `.btn .btn-primary` |
| Card | `.card .shadow-sm` |
| Input Field | `.form-control` |

##  MCP Integration
- **Playwright MCP:** Capture screenshots of the Figma design (using the readonly link) and extract visual details.
- **Filesystem MCP:** Save extracted tokens to SCSS variables and commit them.
- **(Optional) Figma MCP:** If installed, fetch variable definitions and component data directly.

##  RateLimit Handling & Fallback
- If any Figma API call fails due to rate limiting (HTTP 429) or token quota exhaustion, **STOP immediately** and output:
  ` ESCALATION REQUIRED. Figma API rate limit reached. Please manually paste a screenshot of the design and the AI will continue extraction.`

- Do **not** retry automatically. Wait for the human to provide a screenshot of the relevant Figma frame(s) and then resume extraction from the screenshot.

##  Escalation Protocol
- If the Figma link is inaccessible, token expired, API returns 401/403, or extraction fails for any reason, STOP and output:
` ESCALATION REQUIRED. Figma extraction issue: <detail>. Architect review needed.`
- If a manual screenshot is provided, use **Playwright MCP** to analyze the image and proceed with token extraction.

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
