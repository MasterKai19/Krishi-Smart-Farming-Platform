# Krishi

A PHP and MySQL agricultural support application connecting farmers, agricultural officers, and administrators in Bangladesh.

## Features

- Farmer and officer registration with administrator approval.
- Role-based dashboards and account management.
- Agricultural consultation appointments with crop photos and in-app notifications.
- Land records, crop seasons, expense tracking, and budget summaries.
- Example agricultural support schemes and officer-reviewed applications.
- Division, district, and thana location selection.

## Requirements

- PHP 8.0 or later with PDO MySQL and Fileinfo enabled.
- MySQL or MariaDB supporting InnoDB, generated columns, JSON validation, and SQL views.
- Apache with `.htaccess` overrides enabled; XAMPP is suitable for local development.
- A database user able to create the database, tables, and views during setup.

The application uses plain PHP, CSS, and JavaScript. There is no Composer or npm build step. GitHub stores the source; GitHub Pages cannot execute this PHP application.

## Local setup

1. Clone or extract this repository into your Apache document root as `krishi` (for example, `C:\xampp\htdocs\krishi`).
2. Copy `app/config.example.php` to `app/config.php` and enter your database host, port, database name, username, and password. The example uses port `3306`; use the port your database actually listens on. The local config is ignored by Git.
3. Start Apache and MySQL/MariaDB. Ensure PHP can write to the project root during installation and to `uploads/` for appointment photos.
4. Visit `http://localhost/krishi/install.php` on your local machine.
5. Enter your own administrator details and a unique password. Setup creates the schema, location reference data, example schemes, and the first administrator. It also creates `installed.lock` to disable repeat installation.
6. Sign in as **Admin**. Register farmer and officer accounts from the application, then approve them from the admin dashboard.

This repository includes no existing accounts, shared login passwords, uploaded photos, or database backups. Use a new, empty database for setup. Existing-installation migrations in `app/migrations/` are separate CLI utilities; review and back up privately before using them.

## Project structure

```text
app/
  config.example.php  Safe configuration template
  database/           Schema, views, and public reference/example data
  docs/               Feature guide
  migrations/         CLI utilities for existing installations
  lib.php             Database, session, and shared helpers
  *.php               Forms, actions, and role-based pages
assets/               CSS, JavaScript, and favicon
uploads/              Runtime photo storage; contents excluded from Git
index.php             Main application entry point
install.php           Initial local setup
land.php              Land and expense workspace
photo.php             Photo delivery endpoint
```

See [the land guide](app/docs/LAND_GUIDE.md) for land and expense workflows.

## Database files

`install.php` loads `schema.sql`, `locations.sql`, `views.sql`, `land_schema.sql`, and `scheme_examples.sql` from `app/database/`. `photo_schema.sql` is retained as a schema utility; new installations already receive its table through `schema.sql`.

Location names and example scheme descriptions are reference content. Example schemes are illustrative and do not establish eligibility or availability of government benefits.

## Publishing to your GitHub repository

Upload the **contents of this folder** to the repository root so this README appears on the repository homepage. Include hidden files, especially `.gitignore`, `app/.htaccess`, and `uploads/.htaccess`. Do not upload the original transfer ZIP or extracted private copy.

For a new, empty GitHub repository, run these commands inside this folder, replacing the remote URL with your own:

```sh
git init
git add .
git status
git diff --cached --stat
git commit -m "Initial public source release"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git push -u origin main
```

Review staged files before committing. `.gitignore` prevents accidental additions of common private files; it does not remove files already committed or sanitize future edits to tracked SQL files. If private data was previously published, remove it from repository history and replace exposed credentials.

## Privacy and deployment notes

- Keep local configuration, database exports, personal records, uploaded photos, logs, and installation state out of Git.
- Keep the Apache access-denial rules for `app/` and `uploads/`. Other web servers need equivalent rules; do not expose these directories directly.
- Run initial setup locally or behind restricted access. Before internet deployment, review authentication, rate limiting, HTTPS, error reporting, database permissions, and server configuration. This packaging pass is not a full application security audit.

## Verification

PHP syntax can be checked locally with `php -l path/to/file.php`. After setup, manually verify administrator sign-in, account approval, appointment booking, photo access, land creation, expense totals, and scheme review with separate farmer and officer sessions.

## License

No license has been selected for this repository. Add a license you are authorized to grant before inviting reuse or redistribution.
