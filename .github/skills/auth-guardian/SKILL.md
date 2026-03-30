---
name: "auth-guardian"
description: "Multi-role authentication with Gates & Policies for Laravel 5.7."


---

# Skill 05: Auth Guardian

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

##  User Roles
| Role | Access Level | 2FA Required |
|------|--------------|--------------|
| Admin | Full system access |  Yes |
| Teacher | Class/grade management |  No |
| Student | View own grades/attendance |  No |
| Parent | View children's data |  No |

##  Gates & Policies (MANDATORY)
Every controller action MUST have:
```php
// In Controller
$this->authorize('view', $student);

// In Policy class
public function view(User $user, Student $student) {
    return $user->id === $student->user_id || $user->hasRole('admin');
}
```

##  MCP Integration
- **Filesystem MCP:** Read `config/auth.php`, verify Policy files exist.
- **MySQL MCP:** Verify roles table exists.

##  Escalation Protocol
- If policies missing or auth misconfigured, STOP and output:
` ESCALATION REQUIRED. Auth/Policy issue: <detail>. Architect review needed.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
