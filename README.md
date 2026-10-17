# PHP Mentored Education Program, Module 11: Advanced PHP

Branch **`php-advanced`**: dependency injection with PHP-DI, a PSR-3 logger
(Monolog), configuration from `.env`, and the Module 10 product inventory
rewritten around a `ProductService` and an injected `PdoConnection`.
Extra Mile: the same operations with Doctrine DBAL.

| Exercise | Where |
|---|---|
| Install `php-di/php-di` | `composer.json` |
| Install a PSR-3 logger (`monolog/monolog`) | `composer.json`, logger factory in `config/container.php` |
| Inject the logger into the box with the DI container | `src/storage/AbstractBox.php` (constructor), `config/container.php` |
| Log every `getCount()` call | `AbstractBox::getCount()` → *"The box currently contains 3 pieces of fruits"* |
| `db_init.php` creates the `products` table with sample data | `db_init.php` |
| `ProductService` handles all product operations | `src/services/ProductService.php` |
| `PdoConnection` (+ interface), injected into `ProductService` by the container | `src/database/`, `config/container.php` |
| Connection parameters from `.env` (`vlucas/phpdotenv`) | `.env.example`, `config/bootstrap.php`, `config/container.php` |
| ProductHandler pages use the service + PDO | `index.php`, `add-product-page.php`, `save-process.php`, `update-product-page.php`, `update-process.php` |
| **Extra Mile:** Doctrine DBAL | `src/services/DbalProductService.php`, switch with `DB_LAYER=dbal` |

## Getting started

Requirements: Docker with Docker Compose (WSL2), nothing else.

```bash
git checkout php-advanced

cp .env.example .env

docker compose up -d --build

docker compose exec -u www-data app composer install
```

4. Create the table and the sample data. Open **http://localhost:8080/db_init.php**
   (or run `docker compose exec app php db_init.php`). It is safe to run again:
   an existing table and existing products are left untouched.
5. Open the inventory at **http://localhost:8080/**.
6. Open the box demo at **http://localhost:8080/box.php**, or run it in the
   terminal with `docker compose exec app php box.php`.

> MySQL is reachable from WSL/HeidiSQL on `127.0.0.1:3307` (user `root`,
> password `root`, database `mep_db_task`).

If your WSL user ID is not 1000 (`id -u`), build with
`docker compose build --build-arg UID=$(id -u) app`, so that PHP can write `logs/`.

## Dependency injection

All wiring lives in **`config/container.php`**. Every entry point starts with

```php
$container = require __DIR__ . '/config/bootstrap.php';
```

`config/bootstrap.php` loads Composer's autoloader and `.env`, validates the
required variables, and builds the PHP-DI container. Classes do not create or
look up their dependencies; they receive them in their constructor:

```text
LoggerInterface          → Monolog Logger (stderr + logs/app.log, {placeholder} support)
Box / ExoticBox          ← LoggerInterface (channel "box")
PdoConnection            ← DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_CHARSET from .env
ConnectionInterface      → PdoConnection
ProductService           ← ConnectionInterface
Doctrine\DBAL\Connection ← the same .env values                       (Extra Mile)
DbalProductService       ← Doctrine\DBAL\Connection                   (Extra Mile)
ProductServiceInterface  → ProductService (DB_LAYER=pdo) or DbalProductService (DB_LAYER=dbal)
```

Every service is created once per request and shared. That is why `Box` is no
longer a self-made singleton with `getInstance()`: its private constructor
could not receive the logger. Now the container creates the box once and
returns the same instance every time (`box.php` checks this:
"Ugyanaz a példány: igen").

## Logging

`Box::getCount()` logs every call at INFO level:

```text
[2026-10-13T17:23:36.663682+00:00] box.INFO: The box currently contains 3 pieces of fruits {"count":3,"box":"MEPDatabaseTask\\storage\\Box"} []
```

The log goes to two places:

- `logs/app.log` in the project folder (the directory is in git, its contents are not)
- stderr: the terminal for `php box.php`, or `docker compose logs -f app` for requests from the browser

`LOG_FILE` and `LOG_LEVEL` in `.env` change the file and the minimum level.

## PDO or Doctrine DBAL (Extra Mile)

The pages depend only on `ProductServiceInterface`, so switching the data
layer is a single setting in `.env`:

```dotenv
DB_LAYER=pdo    # ProductService + PdoConnection (prepared statements)
DB_LAYER=dbal   # DbalProductService + Doctrine DBAL (QueryBuilder)
```

The header of every page shows which layer is active.

## Project structure

```text
├── .env.example            configuration template (copy to .env, which is git-ignored)
├── config/
│   ├── bootstrap.php       autoloader + .env + container, used by every entry point
│   └── container.php       PHP-DI definitions
├── src/
│   ├── database/           ConnectionInterface, PdoConnection
│   ├── services/           ProductServiceInterface, ProductService, DbalProductService
│   ├── models/             Product, ProductForm
│   ├── storage/            Box, ExoticBox, AbstractBox (+ exceptions)   ← from 08_exercises
│   └── fruits/             Fruit and its subclasses                     ← from 08_exercises
├── templates/              shared page header and footer
├── logs/                   app.log (not committed)
├── db_init.php             creates and seeds the products table
├── box.php                 DI + logger demo for the box
├── index.php               product list (10 per page)
├── add-product-page.php → save-process.php
└── update-product-page.php → update-process.php
```

nginx serves the project root, so `nginx.conf` answers 404 for `.env`, dotfiles,
`config/`, `src/`, `templates/`, `vendor/`, `logs/` and the build files.

## Changes compared to Module 10

- The pages no longer contain database credentials; everything comes from `.env`.
- `ProductRepository` was replaced by `ProductService`, which handles all operations.
- The list now has pagination links, created/updated dates and an Update button per product.
- Validation counts characters (`mb_strlen`) instead of bytes, so names with accented letters are accepted up to 64 characters.
- Redirects after POST use `303 See Other` instead of `301`.
