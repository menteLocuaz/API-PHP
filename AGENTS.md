# AGENTS.md

## Project

Custom PHP API (no framework). Dependency wiring and routing are manual.

## Structure

- `public/index.php` — entry point
- `config/app.php` — loads `.env`, defines `config()` helper
- `app/Controllers/RoutesController.php` — only controller; includes `app/routes/api.php`
- `app/routes/api.php` — static JSON response, not a real router
- `app/Database/` — database access layer (Connection, QueryBuilder, sub-builders)
- `app/Models/` — domain models (GetModel delegates to Database layer)
- `docs/generate.php` — Swagger/OpenAPI spec generator
- `src/` — empty, unused
- `storage/logs/php.log` — error log destination

## PSR-4

`Arancamon\ApiPhp\` → `app/`

Namespace is `Arancamon\ApiPhp\Controllers\` for controllers.

## Dev Server

```bash
php -S localhost:9090 -t public
```

Shorthand: `bash serve.sh` (Linux) or `serve.bat` (Windows).

## Tests

Framework: **Pest PHP 4.x**.

Tests in `tests/Unit/`. `.gitignore` excludes both `phpunit.xml` and `phpunit.xml.dist`, but `phpunit.xml.dist` exists locally for running tests.

Run: `./vendor/bin/pest`

## OpenAPI / Swagger

Generate: `php docs/generate.php` or `./vendor/bin/openapi app/ --output public/swagger/openapi.json`

Uses PHP 8 attributes scanned from `app/`. Output: `public/swagger/openapi.json`

## Known Issues

- `public/index.php` — autoloading happens before `use` statement, but the class `"RoutesController"` is not found at runtime (`storage/logs/php.log`). The `config/app.php` also double-requires `vendor/autoload.php`.
- `composer.lock` is in `.gitignore` (unusual — builds not reproducible).
- No database is configured despite `illuminate/database` being installed.
- Lowercase `app/controllers/` (empty) and uppercase `app/Controllers/` (used) coexist — follow the PSR-4 namespace convention.

## PHP

Requires **PHP ^8.3** (from dependency constraints). Current env: **PHP 8.5**.
