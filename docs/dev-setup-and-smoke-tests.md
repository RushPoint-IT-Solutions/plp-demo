# PLP Demo Setup and Smoke Tests

## One-command setup

```bash
php artisan migrate:fresh --seed
```

## Seeded login accounts

- admin / password (registrar bootstrap account, no forced reset)
- registrar / registrar (registrar demo account)
- faculty / faculty (faculty demo account)
- student / student (student demo account)
- 1234567891012 / PLP-1234567891012 (student first-login reset account)

## Expected first-login behavior

- Accounts created by registrar and seeded first-login accounts are set with force_password_reset = true.
- After login, they are redirected to /password/setup.
- On successful password update, the flag is cleared and user returns to module dashboard.

## Smoke-test checklist

1. Login as admin and open registrar sidebar.
2. Open Faculty Management > Add New Faculty.
3. Create a faculty, capture generated temporary password, and confirm redirect succeeds.
4. Open Student Management > Student Enrollment.
5. Add a student, capture generated temporary password, and confirm redirect succeeds.
6. Login using the newly created student or faculty account and verify first-login password setup screen appears.
7. Complete password reset and verify module page loads normally.

## Quick diagnostics

```bash
php artisan route:clear
php artisan view:clear
vendor\\bin\\phpunit --filter=RouteNameResolutionTest
vendor\\bin\\phpunit --filter=ForcePasswordResetFlowTest
```
