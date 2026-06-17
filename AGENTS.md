# AGENTS.md

## Project

Custom PHP API (no framework). Manual wiring throughout.

## Entry Point

`public/index.php` loads `vendor/autoload.php`, then `config/app.php` (which also requires autoload a second time), calls `Connection::Connect()` (will fail without PostgreSQL running), then dispatches `RoutesController` → `app/Routes/api.php`.

## PSR-4

`Arancamon\ApiPhp\` → `app/`

## Architecture

`app/Routes/api.php` — first URI segment is the table name. Dispatches to HTTP-method Services files (`GetServices.php`, `PostServices.php`, etc).

Layer chain: `Routes/api.php` → `Services/*.php` → `Controllers/*.php` → `Models/*.php` → `Database/*.php` (PDO)

## Dev Server

```bash
php -S localhost:9090 -t public
```

Shorthand: `bash serve.sh` (Linux) or `serve.bat` (Windows).

Apache rewrite rules in `public/.htaccess` also route everything to `public/index.php`.

## Tests

**Pest PHP 4.x** in `tests/Unit/`. Run: `./vendor/bin/pest`

`phpunit.xml.dist` exists locally but is `.gitignore`d.

## OpenAPI / Swagger

```bash
php docs/generate.php
# or
./vendor/bin/openapi app/ --output public/swagger/openapi.json
```

PHP 8 attributes scanned from `app/`. Output: `public/swagger/openapi.json`.

## Database

PostgreSQL 16 via `docker-compose.yml` (`postgres:16`, port 5432, `arctic`/`sa`/`52UYT`).

## Known Issues

- `config/app.php` double-requires `vendor/autoload.php` (already loaded by `public/index.php`).
- `Connection::connect()` in `public/index.php` throws if PostgreSQL is not running — startup fails.
- `composer.lock` is `.gitignore`d — builds not reproducible.
- `app/Database/QueryOptions.php` exists as a DTO but is unused by the current code.
