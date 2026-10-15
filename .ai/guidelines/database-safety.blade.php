# Database Safety Protocol

- PRODUCTION/STAGING DATA EXISTS: The database contains live and sensitive records. Never perform destructive operations blindly.
- MANDATORY BACKUP BEFORE MODIFICATION:
  - Before running ANY destructive migration, `migrate:fresh`, `migrate:reset`, `db:wipe`, or schema altering commands (`ALTER TABLE`, dropping columns/tables), you MUST create a database backup dump first.
  - Run a backup via Artisan command or database CLI (e.g., `php artisan db:backup` or a MySQL/PostgreSQL dump into a local `/storage/app/backups/` directory) before proceeding.
  - If a migration involves modifying or dropping an existing column that contains data, write a data-migration step or save the existing column contents into a temporary/backup table before altering the schema.
- ZERO DATA LOSS: Prefer non-destructive additive migrations (`nullable()` new columns, soft-deletes) over truncating or recreating tables.
- NEVER run `php artisan migrate:fresh` or `php artisan db:seed` without explicit user permission.
