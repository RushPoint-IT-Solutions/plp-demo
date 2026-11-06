# Bundled PLP migration data

These three XLSX exports are the fixed source payload for `2026_11_06_000002_migrate_legacy_plp_data.php`.

Commit this entire directory along with the migration and application changes. Running `php -d memory_limit=512M artisan migrate --force` on the MySQL server validates the bundled files and loads eligible records automatically. No files need to be placed in `storage/app/imports/plp`, and no separate import command is needed.

The migration verifies the exact SHA-256 hash of each file before writing. Do not replace these exports with later files; later datasets need their own reviewed data migration. Original source files include excluded rows; the migration applies the cancellation, identity, relationship, and duplicate validation rules before loading any business record.

Existing records are skipped without updates. New grades may be linked to a verified existing student. Source provenance and validation reports are retained. The data migration does not automatically delete records on rollback.
