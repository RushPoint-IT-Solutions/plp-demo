---
name: "api-architect"
description: "API endpoints for future mobile parent app."


---

# Skill 12: API Architect

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

##  Authentication
- Use API tokens (Laravel 5.7) or OAuth/Passport if already installed; prefer short-lived tokens.
- Store tokens in `api_token` (or use Passport) and require `Authorization: Bearer <token>` header.

##  Rate Limiting (Laravel 5.7)
Use the `throttle` middleware for API routes (Laravel 5.7):
```php
// routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('v1/grades', 'Api\\GradesController@index');
    Route::get('v1/attendance', 'Api\\AttendanceController@index');
});
```

##  API Versioning
```text
GET /api/v1/grades
GET /api/v1/attendance
GET /api/v1/fees
```

##  Response Format (CONSISTENT)
All endpoints must return the same envelope:
```json
{
  "status": 200,
  "success": true,
  "data": {}
}
```

##  MCP Integration
- **Filesystem MCP:** Verify API routes in `routes/api.php` and controllers under `app/Http/Controllers/Api/`.
- **HTTP MCP:** Test endpoints with `curl`, Thunder Client, or Postman; verify auth header and status codes.
- **Security MCP:** Verify token expiry, HTTPS enforcement, and CORS settings.

##  Escalation Protocol
- If API auth is misconfigured, rate limiting absent, or endpoints return 5xx errors, STOP and output:
  ` ESCALATION REQUIRED. API issue: <detail>. Architect review needed.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
