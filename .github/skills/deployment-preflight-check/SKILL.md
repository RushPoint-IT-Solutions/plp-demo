---
name: "deployment-preflight-check"
description: "Runs final sanity checks before production deployment for Laravel 5.7. Verifies environment, cache optimization, migration reversibility."
---

# Deployment Preflight Check (Laravel 5.7)

## Purpose
Catch configuration mistakes before going live.

## Required MCP Tools
- CLI
- Filesystem

## Workflow

1. **Environment Configuration**
   - Check `.env` for `APP_ENV=production`, `APP_DEBUG=false`.
   - Verify `APP_URL` is set.

2. **Cache Optimization (Laravel 5.7 compatible)**
   - Run `php artisan config:cache`
   - Run `php artisan route:cache`
   - Note: `event:cache` is not available in Laravel 5.7; skip.

3. **Migration Reversibility**
   - Run `php artisan migrate:rollback --pretend` to verify `down()` methods.

4. **Storage Link**
   - Ensure `public/storage` symlink exists; if not, `php artisan storage:link`.

5. **Queue Configuration**
   - Suggest switching from `sync` to `database` for production.

6. **Report**
   - Save to `.security-audits/preflight-report.txt`.

## STOP COMMAND
WAITING_FOR_HUMAN_OK