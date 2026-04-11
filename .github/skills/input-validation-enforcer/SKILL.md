---
name: "input-validation-enforcer"
description: "Ensures all incoming data is validated. Generates missing Laravel Form Request classes with strict rules based on DB schema. Compatible with Laravel 5.7 / PHP 7.4."
---

# Input Validation Enforcer

## Purpose
Eliminate unvalidated input that can lead to SQL injection, XSS, mass assignment, or business logic errors. Enforce the use of Laravel's Form Request validation for all non‑trivial user input.

## Required MCP Tools
- Filesystem (`read_file`, `write_file`, `search_files`)
- Database MCP (to read column types and nullable constraints)
- CLI / Terminal (to run `php artisan make:request`)

## Workflow (Steps 1–6)

### 1) Controller Input Usage Scan
- Recursively scan all files in `app/Http/Controllers`.
- Search for patterns:
  - `$request->input('...')`
  - `$request->get('...')`
  - `$request->all()`
  - `$request->only(...)`
  - `$request->except(...)`
  - `$request->validate(...)` (already validated)
- Also detect direct access to `$request->field` (magic getter).
- **Output:** Create a map: `controller_method => list of fields used`.

### 2) Validation Presence Check
- For each controller method that uses input fields:
  - Check if the method type‑hints a custom Form Request class.
  - If not, check if the method body contains `$this->validate()` or `$request->validate()`.
  - If neither, flag as **MISSING VALIDATION**.
- **Action:** Generate a list of controllers/methods requiring attention.

### 3) Form Request Generation (for Missing Cases)
- For each flagged method, determine the relevant model from the controller name or route.
- Use Database MCP to fetch column details for that model's table:
  - Column name, data type, nullable, max length (for strings).
- Use `ui-data-contract.json` (if available) to supplement with expected frontend fields.
- **Action:** Run `php artisan make:request Store{Model}Request` (or `Update{Model}Request`). Laravel 5.7 supports this command.
- Populate the `rules()` method with appropriate validation:
  - `string|max:255` for VARCHAR columns.
  - `integer|exists:table,id` for foreign keys.
  - `email|unique:users,email` for email fields.
  - `required` unless column is nullable or has default.
  - For file uploads: `file|mimes:jpg,png,pdf|max:2048`.
- **Security:** Explicitly exclude sensitive fields like `is_admin`, `role_id` from `$request->validated()` unless the action is explicitly authorized for admin use.

### 4) Controller Refactoring
- Modify the controller method to type‑hint the new Form Request.
- Replace any manual validation or direct input access with `$request->validated()`.
- Example (Laravel 5.7 syntax):
  ```php
  public function store(StoreStudentRequest $request)
  {
      $validated = $request->validated();
      Student::create($validated);
      // ...
  }
  ```

### 5) Additional Security Checks
- Ensure that any `$request->file()` calls have validation for MIME type and size.
- For `unique` rules, ensure they properly ignore the current record on update (e.g., `Rule::unique('users')->ignore($user->id)`).
- For `exists` rules, verify they reference the correct table and column.

### 6) Report Generation
- Save a detailed report to `.security-audits/validation-enforcer-report.md`.
- Include:
  - List of controllers/methods that were missing validation.
  - List of Form Request classes generated.
  - Any manual follow‑up actions required (e.g., complex custom rules).
- **Action:** After generating, prompt the developer to review the new Form Request rules for business logic accuracy.

## STOP COMMAND
When all validation has been enforced and report saved, output exactly:
```
INPUT_VALIDATION_ENFORCEMENT_COMPLETE
WAITING_FOR_HUMAN_OK
```