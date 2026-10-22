# BoardNest — Student Accommodation Platform

BoardNest is a group boarding-house accommodation web application using PHP, HTML, CSS, vanilla JavaScript, Apache and MySQL/MariaDB. The landlord registers a boarding house and its rooms; there is no alternative building-type choice.

## Local run (XAMPP)

1. Place this project at `C:\xampp\htdocs\boardnest-merged`.
2. Start Apache and MySQL in XAMPP. Ensure Apache `mod_rewrite` and `.htaccess` overrides are enabled.
3. Configure the existing local database connection in `config/db.php`.
4. For an existing, patched `boardnest` database, back up your data and run `BoardNest_Boarding_House_Existing_DB_Migration.sql` **once** in phpMyAdmin; it restricts the property type to boarding houses without deleting records. Do **not** reimport `schema.sql` into an existing DB. For a completely new database, `schema.sql` provides the 22-table Landlord-compatible baseline (includes cities lookup), 2 views and 11 triggers (no seeded users).
5. Open `http://localhost/boardnest-merged/` for the landing page.
6. Landlord: `http://localhost/boardnest-merged/public/landlord/login`.
7. Field Agent: `http://localhost/boardnest-merged/public/field_agent/login.php`.
8. Group login: `http://localhost/boardnest-merged/login.php`.

## Source layout

- `public/landlord/`: Landlord HTTP entry point, rewrite rules, client CSS/JS and public image-upload directory.
- `modules/landlord/mvc/`: Landlord routing, controllers, services, models, views, security helpers and shared layouts.
- `public/field_agent/`, `src/field_agent/`: Field Agent interface and backend handlers.
- `modules/admin/`, `modules/announcements/`, `public/admin/`, `src/admin/`: Admin functionality and scaffolding.
- `public/student/`, `src/student/`: Student module scaffolding and partial implementation.
- `config/`, `includes/`: Shared connection and session helpers.
- `public/assets/`, `index.html`, `index.css`: Landing page and shared assets.
- `schema.sql`: Fresh-install schema for the currently tested Landlord database baseline; use only when `boardnest` database does not already exist. It deliberately refuses to create over an existing DB.
- `BoardNest_Boarding_House_Existing_DB_Migration.sql`: One-time migration for existing patched `boardnest` DBs. Existing non-boarding property records must be reviewed manually before the migration can succeed.
- `modules/landlord/mvc/database/migrations/landlord_panel_patch_REFERENCE_ONLY.sql`: Landlord database patch reference. Review prerequisites and current schema before any application.

## Current integration limitations

- Student booking submission and Admin listing-approval action placeholders are incomplete.
- Some Field Agent/Admin SQL uses a different schema than the tested Landlord workflows, and may require separate group-wide integration migrations. The fresh-install schema is NOT certified as a complete schema for those unfinished modules.
- PayHere/payment integration is not verified.
- The shared login includes a legacy password-compatibility path that requires security review before public deployment.
- User uploads and database records are not bundled; server-created file storage must be backed up separately.

This project is suitable for continued group development and a local demonstration with the correct database. It has **not** been certified as production-deployment-ready.

## License

See `LICENSE`. Follow your university's project-submission and attribution requirements.


## City selection (Landlord)

The Add/Edit Boarding House form loads city names from `cities` and stores only the
selected `cities.id` in `properties.city_id` as a foreign key. Existing/Admin area
code references are compatible with `cities(id,name,district,province)`.

- Fresh installation: import `schema.sql` into a server without an existing `boardnest` DB.
- Existing `boardnest` with the Boarding House schema: back up first, **review old
  city_id values and addresses**, then run `BoardNest_Existing_DB_Cities_Migration.sql`
  once. A read-only precheck SQL is supplied separately from the project ZIP. IDs originally typed manually must not be silently interpreted as locations.
- Demo/clean SQLs: see accompanying city-enabled SQL files in the release bundle.
- This update does NOT implement remaining Field Agent `properties.city` references or
  other unfinished cross-module tables. Those need group integration work.
