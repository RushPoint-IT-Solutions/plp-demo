---
name: "image-to-component"
description: "Convert UI screenshots to Bootstrap 4 / Vue 2 components or Laravel Blade views."
---

# Skill 15: Image to Component

## 🚨 LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

## 🚫 Explicitly Forbidden PHP 8+ Features
- `match` expressions, Union types (`string|int`), Nullsafe operator (`?->`), Named arguments, Constructor property promotion, Arrow functions (`fn() =>`), Typed properties, or Null-coalescing assignment (`??=`).

## 👁️ Vision AI Analysis Requirements
Analyze the provided screenshot and extract:
1. **Layout Structure:** Flexbox, grid, and spacing.
2. **Components:** Cards, buttons, inputs, modals.
3. **Typography & Colors:** Extract accurate hex codes.
4. **Icons/Images:** Use placeholder text or common FontAwesome classes (e.g., `<i class="fas fa-user"></i>`) for icons you see. Do not attempt to generate base64 images.

## 📦 Output Requirements
- **Responsive:** Must be mobile-first and visually accurate down to 320px widths.
- **SCSS:** Extract all colors and specific padding/margins into SCSS. Use variables for colors. NO hardcoded inline styles.
- **Markup:** Ask the user if they want a **Vue 2 Component** (`.vue`) or a **Laravel Blade View** (`.blade.php`).

## 🛠️ MCP Integration Workflow
1. **Analyze:** Use native vision capabilities to process the uploaded image.
2. **Generate:** Write the HTML/Vue/Blade markup and the SCSS.
3. **Filesystem MCP (Write):** - Save the markup to the appropriate `resources/views/` or `resources/js/components/` folder.
   - Save the SCSS to `resources/assets/sass/components/`.
4. **Filesystem MCP (Update):** Append an `@import` statement for the new SCSS file into the main `app.scss` (usually located at `resources/assets/sass/app.scss`).

## ⚠️ Escalation Protocol
- If the component structure is too blurry or unclear to replicate, STOP and output:
  `🚨 ESCALATION REQUIRED. Component extraction unclear: <detail>.`

## 🛑 STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when all files are generated and updated.