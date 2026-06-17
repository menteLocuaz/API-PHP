# AGENTS.md

Custom PHP API (no framework). Manual wiring throughout.

## Entry Point

`public/index.php` → `vendor/autoload.php` → `bootstrap/app.php` (dotenv, `config()` helper, error config, UTC timezone) → `RoutesController` → `app/Routes/api.php`. DB connection is lazy — first access triggers PDO singleton (`app/Database/Connection.php`).

## PSR-4

`Arancamon\ApiPhp\` → `app/`. `declare(strict_types=1)` throughout.

## Routing & Architecture

`app/Routes/api.php` — first URI segment is the table name. Dispatch per HTTP method via `match` to Services files.

Layer chain: `Routes/api.php` → `Services/*.php` → `Controllers/*.php` → `Models/*.php` → `Database/*.php` (custom QueryBuilder + PDO Connection)

Naming quirk: files for POST operations are `PosController.php` / `PosModel.php` (not `Post`).

### Query Parameters

Most features are driven by query string params, not URL segments:

| Param | Used by | Purpose |
|-------|---------|---------|
| `select`, `orderBy`, `orderMode`, `startAt`, `endAt` | All GET | Column selection, sorting, pagination |
| `linkTo`, `equalTo` | GET | Filter by column(s) — `_`-separated for multi-field |
| `rel`, `type` | GET | JOIN relations (`rel` = tables, `type` = join types, `_`-separated) |
| `search` | GET | ILIKE search across `linkTo` columns |
| `between1`, `between2`, `filterTo`, `inTo` | GET | Range filter with optional IN clause |
| `register=true`, `login=true`, `suffix` | POST | Auth register/login flows |
| `token`, `table`, `suffix` | POST/PUT/DELETE | Token validation (default `table=users`, `suffix=user`) |
| `token=no`, `except` | POST/PUT/DELETE | Skip token check for specific fields |
| `id`, `nameId` | PUT/DELETE | Row identification for updates/deletes |

## Response Format

All JSON via `app/Http/Response.php`:

```json
{ "status": 200, "total": N, "results": [...] }
```

On error, `results` field contains the error string (no separate `error` key). `total` is omitted on non-200.

## Auth

- **Rate limiter**: `RateLimiterMiddleware` runs first in `api.php`. File-based, per-IP window. Config: `RATE_LIMIT_MAX` / `RATE_LIMIT_WINDOW` env vars (default 60 req/60s). Data in `storage/rate-limits/`. 5% chance of GC per request.
- **API key**: `Authorization` header compared to `API_KEY` env var (`AuthService::apiKey()`). **If `API_KEY` is unset/empty, any request without a header passes** (empty string matches empty string).
- **Public tables**: `AuthService::publicAccess()` returns `['']` — **no tables are actually public**. The middleware short-circuits early for any table match.
- **JWT**: Hardcoded secret `dfhsdfg34dfchs4xgsrsdry46` in `PosController.php` (not from env). `JwtService::jwt()` generates payload. Tokens stored in `token_{suffix}` / `token_exp_{suffix}` columns.

## Dev Server

```bash
php -S localhost:9090 -t public
# or: bash serve.sh
```

Apache rewrite via `public/.htaccess`. Root `index.php` and `.htaccess` are dead.

## Tests

**Pest PHP 4.x** in `tests/Unit/`:

```bash
./vendor/bin/pest
```

Tests are unit-only (no DB required). Uses `beforeEach` to reset env and `Connection::reset()`.

## OpenAPI / Swagger

```bash
php docs/generate.php
# or
./vendor/bin/openapi app/ --output public/swagger/openapi.json
```

Output: `public/swagger/openapi.json`.

## Database

PostgreSQL 16 via `docker-compose.yml` (port 5432, `arctic`/`sa`/`52UYT`). No migration tooling — schema managed out of band.

Uses `information_schema.columns` queries for column validation and `ILIKE` for case-insensitive search.

## Storage

PHP error logs → `storage/logs/php.log`.

## Known Issues

- `config/app.php` is dead code (bootstrap moved to `bootstrap/app.php`).
- `Connection::connect()` throws if PostgreSQL is not running — startup fails.
- `composer.lock` is `.gitignore`d — builds not reproducible.
- `app/Database/QueryOptions.php` DTO unused by current code.
- `app/Views/` is empty.
- **JWT secret hardcoded** in `PosController.php` (lines 47, 89, 112).
- `APP_CORS` env var referenced by `Cors.php` but missing from `.env.template`.
