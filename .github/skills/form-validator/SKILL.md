---
name: "form-validator"
description: "Form Request validation for Laravel 5.7."


---

# Skill 06: Form Validator

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.
- **Auth:** `php artisan make:auth` (Laravel 5.7 standard)  DO NOT use Breeze/Jetstream

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Validation Rules (Form Requests)
- Move ALL validation out of controllers into FormRequest classes.
- Every POST request MUST have corresponding FormRequest class.
- Custom rules: `student_id` must exist in students table.
- Error messages in `resources/lang/en/validation.php`.

##  Example (Laravel 5.7 Compatible)
```php
class StoreStudentRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|ends_with:@gmail.com',
            'grade' => 'required|numeric|min:1|max:5',
        ];
    }
}
```
##  MCP Integration
- **Filesystem MCP:** Verify FormRequest files exist in `app/Http/Requests/`.
- **MySQL MCP:** Verify referenced tables/columns exist for `exists:` rules.

##  Escalation Protocol
- If validation in controller detected (not FormRequest), STOP and output:
` ESCALATION REQUIRED. Validation in controller detected. Move to FormRequest.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
