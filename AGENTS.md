# AGENTS.md

Custom PHP API (no framework). Manual wiring throughout.

## Entry Point

`public/index.php` → `vendor/autoload.php` → `bootstrap/app.php` (dotenv, `config()` helper, error config, timezone) → `RoutesController` → `app/Routes/api.php`. Connection is lazy — first DB access triggers PDO singleton.

## PSR-4

`Arancamon\ApiPhp\` → `app/`

`declare(strict_types=1)` used throughout.

## Routing & Architecture

`app/Routes/api.php` — first URI segment is the table name. Dispatch per HTTP method via `match` to Services files.

Layer chain: `Routes/api.php` → `Services/*.php` → `Controllers/*.php` → `Models/*.php` → `Database/*.php` (custom SQL QueryBuilder + Connection PDO)

The `QueryBuilder` delegates to builders in `app/Database/Builders/`: `SelectBuilder`, `WhereBuilder`, `JoinBuilder`, `RangeBuilder`, `SearchBuilder`.

Naming quirk: controller/model files are `PosController.php` / `PosModel.php` (apparent typo — not `Post`).

## Auth

- **Rate limiter**: `app/Middlewares/RateLimiterMiddleware.php` runs first in `api.php`. File-based, window per IP. Configured via `RATE_LIMIT_MAX` / `RATE_LIMIT_WINDOW` env vars (default 60 req/60s). Data lives in `storage/rate-limits/`.
- **API key**: `Authorization` header compared to `API_KEY` env var (`app/Security/AuthService.php:apiKey()`).
- **Public tables**: tables listed in `AuthService::publicAccess()` (currently `['']`) can be read without a key.
- **JWT**: `JwtService::jwt()` generates payload; `AuthService::tokenValidate()` checks token against DB columns `token_{suffix}` / `token_exp_{suffix}`.

## Response Format

All JSON via `app/Http/Response.php`:
```json
{ "status": 200, "results": [...], "total": N }
```

## Dev Server

```bash
php -S localhost:9090 -t public
# or: bash serve.sh
```

Apache rewrite rules in `public/.htaccess` also route everything to `public/index.php`.

## Tests

**Pest PHP 4.x** in `tests/Unit/`. Run:

```bash
./vendor/bin/pest
```

## OpenAPI / Swagger

```bash
php docs/generate.php
# or
./vendor/bin/openapi app/ --output public/swagger/openapi.json
```

Output: `public/swagger/openapi.json`.

## Database

PostgreSQL 16 via `docker-compose.yml` (port 5432, `arctic`/`sa`/`52UYT`).

## Storage

PHP error logs → `storage/logs/php.log`.

## Known Issues

- `config/app.php` is dead code (bootstrap moved to `bootstrap/app.php`).
- `Connection::connect()` throws if PostgreSQL is not running — startup fails.
- `composer.lock` is `.gitignore`d — builds not reproducible.
- `app/Database/QueryOptions.php` DTO unused by current code.
- `app/Views/` is empty.
