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

## Minimalist Validation Modal Standard
Use this when showing blocking form-validation errors (for example: missing `Time From`/`Time To` slots in Academic Calendar forms).

- Do not use native `alert(...)` for validation failures.
- Use a dedicated modal overlay with:
  - short uppercase title,
  - concise actionable message,
  - one clear primary action button (`Got It`/`Close`).
- Modal behavior must support:
  - click outside to close,
  - `Escape` to close,
  - focus visibility on the primary action.
- Keep style minimal and consistent with PLP green/white surfaces:
  - soft dark backdrop,
  - compact dialog box,
  - subtle error badge/icon,
  - no heavy gradients or loud animations.
- Place all modal styles in SASS (`resources/sass/_pages-overrides.scss` or page-scoped partial).
- Place modal logic in JS assets (or existing page JS block only when legacy page structure still requires it).
- Validation copy must mention the exact required fields (example: `Please add both Time From and Time To time slots for this event.`).

## Action Menu Styling Standard
Use this for row-level action controls (3-dot button, dropdown actions, and action modals) across Registrar/Admin tables.

- Keep a single reusable trigger pattern for row actions:
  - Trigger class: `.apst-action-btn`
  - Menu class: `.apst-dropdown`
  - Destructive button class: `.apst-del-btn`
- Style action triggers as compact icon buttons with:
  - consistent hit area (minimum 32x32),
  - clear hover/focus ring,
  - visible active/open state.
- Action dropdown requirements:
  - rounded panel,
  - readable row spacing,
  - clear separators only when needed,
  - stable stacking (`z-index`) above table scroll wrappers.
- Preserve semantic intent by action type:
  - `View`: neutral/informational state,
  - `Edit`: primary/affirmative state,
  - `Delete`: destructive state (red-tinted hover + danger label).
- Behavior contract:
  - `View` must open a read-only summary/details modal.
  - `Edit` must open editable controls.
  - Never route `View` directly to `Edit` handlers.
  - Close any open action menu when scrolling, clicking outside, or opening another row menu.
- Do not place action-state styles inline in Blade. Keep them in SASS page scopes (for example `.sc-page .apst-action-btn ...`) and reuse shared class names.
- For notification items that represent announcements, prefer opening a detail modal (title + message) instead of forced page redirection, while preserving dismiss/read controls.

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

## Calendar & File Input Styling

Standardize the visual presentation and accessible behavior of date pickers and file chooser controls so pages match the project green/white theme and behave consistently across browsers.

Goals:
- Visual parity with the PLP theme (green accents, soft white panels).
- Large tap targets and clear focus outlines for accessibility.
- Graceful browser fallbacks where native controls differ.

Recommended variables (place in your global variables partial, e.g. `_variables.scss`):

```scss
$plp-green: #0f7a4e;
$plp-green-dark: #0c653f;
$plp-muted: #b8d7c8;
$plp-dark: #1f4735;
```

Example page override (copy into `resources/sass/_pages-overrides.scss` under the appropriate page scope such as `.page-system-config-configuration`):

```scss
// Calendar and file chooser styling - page-specific override
.page-system-config-configuration {
  .req-modal-input[type="file"] {
    width: 100%;
    min-height: 38px;
    border: 1px solid $plp-muted;
    border-radius: 10px;
    background: linear-gradient(180deg, #ffffff 0%, #f4fbf7 100%);
    color: $plp-dark;
    padding: 5px 8px;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .req-modal-input[type="file"]::file-selector-button,
  .req-modal-input[type="file"]::-webkit-file-upload-button {
    appearance: none;
    border: 0;
    border-radius: 8px;
    background: $plp-green;
    color: #ffffff;
    font-weight: 600;
    padding: 7px 14px;
    margin-right: 10px;
    cursor: pointer;
    transition: background-color 0.18s ease;
  }

  .req-modal-input[type="file"]:hover::file-selector-button,
  .req-modal-input[type="file"]:hover::-webkit-file-upload-button {
    background: $plp-green-dark;
  }

  .req-modal-input[type="file"]:focus,
  .req-modal-input[type="file"]:focus-visible {
    outline: none;
    border-color: $plp-green;
    box-shadow: 0 0 0 3px rgba(15, 122, 78, 0.16);
  }

  .req-modal-input[type="date"] {
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
    min-height: 38px;
    border: 1px solid $plp-muted;
    border-radius: 10px;
    background-color: #ffffff;
    background-image:
      linear-gradient(180deg, rgba(236, 249, 242, 0.45) 0%, rgba(255, 255, 255, 0) 100%),
      url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230f7a4e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
    background-repeat: no-repeat, no-repeat;
    background-position: 0 0, right 12px center;
    background-size: auto, 16px 16px;
    color: $plp-dark;
    padding-right: 42px;
    cursor: pointer;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .req-modal-input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0;
    width: 20px;
    height: 20px;
  }

  .req-modal-input[type="date"]:hover {
    border-color: lighten($plp-muted, 6%);
  }

  .req-modal-input[type="date"]:focus,
  .req-modal-input[type="date"]:focus-visible {
    outline: none;
    border-color: $plp-green;
    box-shadow: 0 0 0 3px rgba(15, 122, 78, 0.16);
  }
}
```

Accessibility & integration notes:

- Ensure each `type="file"` control has an associated `<label>` or `aria-label` so screen-readers present a clear control name.
- Keep the native `<input type="file">` in the DOM (styled via the rules above) so form submissions and progressive enhancement work without JS.
- Hiding the calendar indicator (`::-webkit-calendar-picker-indicator`) is acceptable when using a decorative SVG background, but keep keyboard focus visible and obvious.
- Test on desktop and mobile browsers; some Android OEM browsers render file/date controls differently—the above rules are progressive enhancements, not hard fallbacks.
- If you need consistent dropdown/listbox behavior for file type lists or date presets, implement a small JS wrapper that toggles a visually matched list while keeping the native inputs for form submission.
- For flatpickr month/year headers, style the closed controls in SASS with explicit selectors such as `.flatpickr-monthDropdown-months`, `.numInputWrapper`, `.arrowUp`, and `.arrowDown` so month selection and year stepping stay visible and on-brand.
- If the browser still renders the month popup list natively, treat that as an OS limitation; keep the closed control polished and, if full theming is required, replace the control with a custom JS listbox instead of forcing native option styling.

Where to place this:

- Add the SCSS snippet to `resources/sass/_pages-overrides.scss` under the appropriate `.page-*` scope for a page.
- Import shared variables into the overrides file (e.g., `@import 'partials/_variables';`).
- After editing, run the usual Mix build: `npm run dev` or `npm run watch` in development to confirm compiled asset is available.

## STOP COMMAND
WAITING_FOR_HUMAN_OK
