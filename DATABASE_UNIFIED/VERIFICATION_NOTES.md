# BoardNest V4 PHP Validation — Verification Scope

- `schema.sql` and `01_FRESH_FULL_SCHEMA_PHP_VALIDATION.sql`: identical fresh installer, 47 tables, 0 CREATE TRIGGER, 0 CREATE VIEW.
- Prefix matches the user-supplied 27-table `newschema.sql` byte for byte.
- SQL DDL retains FK, UNIQUE and CHECK constraints. Cross-table transition logic is in PHP.
- New `03_REALISTIC_FICTIONAL_MOCK_DATA.sql` requires a clean schema and city seed; it is not a migration.
- Automated static checks are provided by `scripts/static_recheck.py` and read-only MySQL audit by `scripts/check_unified_db.php`.
- **Not verified here:** MariaDB importing or data seeding against an actual server; concurrent HTTP test; group Student/Admin/Field Agent flows. Verify locally before trusting the package for production.
