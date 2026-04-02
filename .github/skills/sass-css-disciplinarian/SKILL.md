---
name: "sass-css-disciplinarian"
description: "Prevents inline CSS/JS in Blade files; enforces SASS/SCSS usage with best practices."
---

# SASS CSS Disciplinarian

## Purpose
Enforce strict separation of concerns for Laravel Blade, SASS, and JavaScript to keep templates clean and prevent styling/script conflicts across contributors.

## Hard Rules (Non-Negotiable)
- NEVER write `<style>` tags in Blade (`.blade.php`) files.
- NEVER write inline `style="..."` attributes in Blade files.
- NEVER write `<script>` tags in Blade files, except for external asset includes using `mix()` or `asset()`.
- NEVER write inline JavaScript handlers in Blade files (`onclick`, `onload`, `onchange`, etc.).
- ALL CSS must go into `.scss` files inside `resources/assets/sass/`.
- ALL custom JavaScript must go into `.js` files inside `resources/assets/js/`.

## SASS Best Practices
- Use variables for colors, fonts, and spacing.
  - Example: `$primary: #007bff;`
- Use nesting carefully; do not nest more than 3 levels deep.
- Use partials like `_header.scss`, `_forms.scss`, `_certificate.scss` and import them in `app.scss` (or a scoped entry file).
- Use BEM naming (`Block__Element--Modifier`) or a clear component-based naming structure.
- Avoid `!important` unless absolutely necessary and documented.
- Keep responsive styles close to their component by placing `@media` rules inside the same selector block.

## Blade + Asset Integration Rules
- Blade should reference compiled assets only.
- CSS include pattern:
  - `<link rel="stylesheet" href="{{ mix('css/app.css') }}">`
- JS include pattern:
  - `<script src="{{ mix('js/app.js') }}"></script>`
- If page-specific styling is required, create a dedicated SCSS file in `resources/assets/sass/`, compile it with Laravel Mix, and include only that compiled CSS in the target Blade.

## Conflict Prevention Checklist
- Prefix or namespace component classes for page-specific modules.
- Do not use global element selectors (`h1`, `p`, `button`) without a parent scope.
- Keep selectors predictable and low-specificity.
- Reuse shared variables and mixins before adding new ones.

## Review Gate
Before finalizing any Blade change:
1. Confirm no `<style>` tag exists in the Blade file.
2. Confirm no inline `style=` attributes exist.
3. Confirm no inline JS handlers exist.
4. Confirm custom styles are in `resources/assets/sass/` and compiled.
5. Confirm custom scripts are in `resources/assets/js/` and compiled.

## STOP COMMAND
WAITING_FOR_HUMAN_OK
