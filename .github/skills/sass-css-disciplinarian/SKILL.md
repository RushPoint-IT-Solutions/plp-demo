---
name: "sass-css-disciplinarian"
description: "Prevents inline CSS/JS in Blade files; enforces SASS/SCSS usage and standardizes dropdown, pagination, and search-bar behavior."
---

# SASS CSS Disciplinarian

## Purpose
Enforce strict separation of concerns for Laravel Blade, SASS, and JavaScript to keep templates clean, prevent styling/script conflicts across contributors, and keep dropdowns, pagination, and search bars consistent across the UI.

## Hard Rules (Non-Negotiable)
- NEVER write `<style>` tags in Blade (`.blade.php`) files.
- NEVER write inline `style="..."` attributes in Blade files.
- NEVER write `<script>` tags in Blade files, except for external asset includes using `mix()` or `asset()`.
- NEVER write inline JavaScript handlers in Blade files (`onclick`, `onload`, `onchange`, etc.).
- ALL CSS must go into `.scss` files inside `resources/sass/` (or `resources/assets/sass/` for legacy modules).
- ALL custom JavaScript must go into `.js` files inside `resources/js/` (or `resources/assets/js/` for legacy modules).

## SASS Best Practices
- Use variables for colors, fonts, and spacing.
  - Example: `$primary: #007bff;`
- Use nesting carefully; do not nest more than 3 levels deep.
- Use partials like `_header.scss`, `_forms.scss`, `_certificate.scss` and import them in `app.scss` (or a scoped entry file).
- Use BEM naming (`Block__Element--Modifier`) or a clear component-based naming structure.
- Avoid `!important` unless absolutely necessary and documented.
- Keep responsive styles close to their component by placing `@media` rules inside the same selector block.

## UI Review Standard
Whenever a Blade page or shared component includes dropdowns, pagination, or search bars, verify them together in the browser before finalizing the change.

- Confirm dropdown list styling matches the shared standard and stays usable at mobile widths.
- Confirm pagination uses the same reusable server-driven logic and preserves active filters or query parameters.
- Confirm search bars behave like searchable list pickers when the source list is large, instead of forcing a long native dropdown.
- Confirm buttons, lists, and pager controls do not clip or overflow on iPhone SE-sized layouts.
- Confirm the interaction logic is consistent across the page: the same component state should open, highlight, select, and close in the same way everywhere.

## Styled Dropdown Standard (Reusable)
Use this pattern whenever dropdown option lists must be visually designed (green highlight, radius, white panel, etc.) and behavior must be consistent across browsers.

- Keep a real `<select>` in the DOM for form submission, backend validation, and graceful fallback.
- Add a custom wrapper with:
  - Trigger button (`data-select-trigger`) for opening/closing.
  - Option panel (`data-select-menu`) using `role="listbox"`.
  - Option buttons using `role="option"` and value mapping via data attributes.
- Style only through SASS classes. Required state classes:
  - `.is-open` for expanded panel.
  - `.is-selected` for the current selected value.
  - `.is-active` for the currently hovered/focused option.
- Interaction requirements:
  - Cursor hover and keyboard focus must move the active highlight to that exact option.
  - Arrow keys move active option.
  - `Enter`/`Space` selects active option.
  - `Escape` closes and returns focus to the trigger.
- Never rely on native browser `<option>` popup styling for final UI design.

## Searchable Dropdown List Standard (Single-Open, Focus-Driven)
Use this for autocomplete/search inputs that render `.smrg-search-dropdown` option lists, especially when a large dropdown should be replaced with a searchable list for students, subjects, sections, faculty, rooms, or any other high-volume entity.

- Canonical Blade component for this project:
  - `resources/views/registrar/components/search-dropdown-input.blade.php`
- Naming contract:
  - Reusable shared classes stay `smrg-search-*` (`.smrg-search-wrap`, `.smrg-search-input`, `.smrg-search-dropdown`, `.smrg-search-option`).
  - Page/module classes use page prefixing (example: `sd-*` for Student Discipline wrappers like `.sd-record-search-wrap`).
  - Dropdown element IDs must be explicit and stable per field (example: `sdCaseTypeDropdown`, `sdCalledByDropdown`) so JS can enforce single-open behavior.
- Keep option lists hidden by default (`display: none`) and only show them on input focus or active typing.
- Open exactly one search dropdown at a time across the page or modal.
- When a different searchable field receives focus, close all other open lists immediately.
- Close lists on click outside, modal close, and Escape key.
- Selecting an option should close the current list and populate the input/hidden value.
- Never keep multiple search dropdowns visible simultaneously; this is a strict UX rule.
- Prefer this pattern over a native `<select>` whenever the option set is large enough to become hard to scan or tap on mobile.
- Search results may be client-filtered or server-filtered, but the interaction model should remain the same.
- The visible list should behave like a lookup panel, not a static dropdown dump.

## Pagination Component Rule
- Pagination styling must be reusable via shared component classes (not page-only selectors).
- Pagination logic should be reusable and server-driven for high-volume tables (thousands of rows):
  - Submit `page`/`per_page` back to backend.
  - Do not render all rows client-side.
  - Keep click handlers generic and configurable by data attributes.
  - Preserve current search and filter state in every pagination link.
  - Keep pagination controls responsive so they do not clip on narrow screens.

## Blade + Asset Integration Rules
- Blade should reference compiled assets only.
- CSS include pattern:
  - `<link rel="stylesheet" href="{{ mix('css/app.css') }}">`
- JS include pattern:
  - `<script src="{{ mix('js/app.js') }}"></script>`
- If page-specific styling is required, create a dedicated SCSS file in `resources/sass/` (or `resources/assets/sass/` in legacy modules), compile it with Laravel Mix, and include only that compiled CSS in the target Blade.

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
4. Confirm custom styles are in `resources/sass/` (or `resources/assets/sass/` in legacy modules) and compiled.
5. Confirm custom scripts are in `resources/js/` (or `resources/assets/js/` in legacy modules) and compiled.
6. For styled dropdowns, confirm active highlight follows current hover/focus target and the list stays inside the viewport.
7. For pagination, confirm links preserve current filters/search terms and remain usable on mobile.
8. For search bars used as large-list substitutes, confirm the results list shows the right matches, closes correctly, and supports keyboard and touch selection.
9. For Student forms, confirm desktop has one vertical scrollbar and print keeps a 2in top form margin.

## STOP COMMAND
WAITING_FOR_HUMAN_OK
