---
name: "backup-guardian"
description: "Automated database backups with cloud storage."
applyTo:
  - "config/backup.php"
  - "storage/app/backups/*"
version: "1.0-legacy"

---

# Skill 19: Backup Guardian

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

##  Backup Schedule
| Frequency | Storage | Retention |
|-----------|---------|-----------|
| Daily | S3 / Google Drive | 30 days |
| Weekly | Local + Cloud | 12 weeks |
| Monthly | Cloud (encrypted) | 12 months |

##  Security Requirements
- Encrypt all backup files at rest.
- Store encryption keys out-of-band (Key Vault / environment secrets).
- Periodically test restore procedures (monthly).

##  Package: spatie/laravel-backup
Install and configure the package (example):
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
php artisan backup:run
```

Configure retention/backup destinations in `config/backup.php` and ensure `.env` contains cloud credentials.

##  MCP Integration
- **Filesystem MCP:** Verify backup files are written to `storage/app/backups/` and that retention cleanup runs.
- **Cloud MCP:** Verify S3/Google Drive credentials (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `GOOGLE_DRIVE_*`) and ensure successful uploads.
- **MySQL MCP:** Test restore into a non-production test database and validate data integrity.

##  Escalation Protocol
- If backups fail, uploads to cloud storage fail, or restores fail during test, STOP and output:
  ` ESCALATION REQUIRED. Backup failure: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
