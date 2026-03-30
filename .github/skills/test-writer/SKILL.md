---
name: "test-writer"
description: "PHPUnit tests for Laravel 5.7 (80% coverage required)."


---

# Skill 16: Test Writer

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

##  Test Coverage Requirements
| Test Type | Minimum Coverage |
|-----------|------------------|
| Feature Tests | All auth flows |
| Unit Tests | Grade calculation logic |
| Browser Tests (Dusk) | Critical user journeys |
| **Overall** | **80% code coverage** |

##  Test Examples (Laravel 5.7 Compatible)
```php
public function test_user_can_login()
{
  $user = factory(App\User::class)->create();
  $response = $this->post('/login', [
    'email' => $user->email,
    'password' => 'password'
  ]);
  $response->assertRedirect('/home');
}
```

##  MCP Integration
- **Filesystem MCP:** Verify test files exist under `tests/Feature` and `tests/Unit` and follow naming conventions.
- **MySQL MCP:** Ensure a test database is configured (prefer sqlite in-memory for CI) and migrations ran before tests.
- **CI MCP:** Run `vendor/bin/phpunit --coverage-text` and collect coverage; fail build if overall coverage < 80%.

##  Escalation Protocol
- If critical tests fail or overall coverage is below 80%, STOP and output:
  ` ESCALATION REQUIRED. Test coverage below 80% or failing critical tests: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
