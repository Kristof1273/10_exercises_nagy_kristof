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






personal notes:

A request travels from the browser to nginx on port 8080, then to PHP-FPM, and finally to a page such as `index.php`.  
Every page starts by loading `config/bootstrap.php`.  
1 This file loads Composer's autoloader,
2 then reads the `.env` file with phpdotenv.  
[The values end up in `$_ENV` !!!!], (and because the loader is immutable, variables that are already set by the environment are not overwritten). The bootstrap checks that all required database variables exist and stops with a clear message if one is missing.  
3 Finally, it builds the PHP-DI container from `config/container.php` and returns it.  
=> As a result, the database credentials live in a single git-ignored `.env` file instead of being hard-coded in several files.

The DI container works like a recipe book: it knows how to build each service and what to inject into it. In `config/container.php`, `env()` reads a value from `.env`, `factory()` builds an object with custom code, [`get()` maps an interface to a concrete implementation, and `autowire()` builds an object from its constructor type hints, while `constructorParameter()` sets a single argument explicitly.]

~~~php
Box::class => autowire()
    ->constructorParameter('logger', get('logger.box'))
~~~
Services are shared within a request. `get()` always returns the same instance, while `make()` creates a new one every time.

In the first part of the exercise, Monolog is configured in the container as the PSR-3 logger. It has a channel name, a processor that fills placeholders such as `{count}` with real values, and two handlers: one writes to stderr, the other to `logs/app.log`. A second definition, `logger.box`, provides the same logger under the channel name "box". The container passes this logger to the `Box` constructor. `Box` hands it to its parent class `AbstractBox` with `parent::__construct()`, and `AbstractBox` stores it in a property. Every call to `getCount()` writes "The box currently contains x pieces of fruits" at INFO level and then returns the count. Internal checks such as `isFull()` call `count()` directly, so only real external calls are logged. Box is no longer a singleton, because its private constructor could not receive any dependencies. The container now guarantees a single shared instance instead.

In the second part, `PdoConnection` handles the database connection. It receives its parameters from the container and knows nothing about `.env` itself. It connects lazily on first use and then reuses the same connection. It turns SQL errors into exceptions, returns rows as associative arrays and uses native prepared statements. If the connection fails, it throws a new exception without the original one, so the password cannot leak through the stack trace. The class is backed by `ConnectionInterface`, which makes the connection replaceable, for example with an SQLite database for tests, without touching any code that uses it.

`ProductServiceInterface` defines the contract for all product operations: saving, updating, fetching one product, listing a page of ten products and counting them. `ProductService` implements this contract with PDO prepared statements. Because the SQL text and the values are sent separately, user input can never become part of the query, which prevents SQL injection. Pagination uses `LIMIT` and `OFFSET` with integer binding, and `Product::fromRow()` converts database rows into `Product` objects in one place. The service replaces the earlier `ProductRepository` and the connections that every page used to create on its own.

The pages only ask the container for the interface. `index.php` calculates the number of pages, keeps the requested page within range and shows messages selected by fixed keys, so a URL cannot inject its own text. `save-process.php` validates the form with `ProductForm`, creates a `Product`, saves it and redirects with status 303. This follows the Post/Redirect/Get pattern, so refreshing the page does not submit the form again. `update-process.php` works the same way, but first checks that the product exists. `ProductForm` counts lengths in characters with `mb_strlen`, so names with accented letters are measured correctly. The `db_init.php` script creates the products table only if it is missing, and inserts 25 sample products only into an empty table, inside a transaction. It uses the same connection from the container, so it reads the same `.env` settings.

For the Extra Mile, Doctrine DBAL adds a layer on top of PDO with connection management and a QueryBuilder. `DbalProductService` implements the same `ProductServiceInterface` using that QueryBuilder. The `DB_LAYER` setting in `.env` tells the container whether to provide the PDO or the DBAL implementation. None of the pages had to change, because they depend only on the interface.

The application runs in three Docker containers: PHP-FPM, nginx on port 8080 and MySQL, which is reachable from the host on port 3307. Inside the Docker network the containers find each other by service name, so the database host is `db` and the port is 3306. The Dockerfile adds Composer and gives the `www-data` user the ID 1000, so PHP can write the log file in the mounted project folder. Because the project root is also the web root, nginx returns 404 for `.env`, `config/`, `src/`, `vendor/` and `logs/`, so these files can never be downloaded through the browser.

Two core ideas run through the whole project. Dependency Injection is a technique: a class receives its dependencies from outside instead of creating them itself. The Dependency Inversion Principle is a design rule: classes should depend on abstractions such as interfaces, not on concrete classes. A `Box` that receives a `LoggerInterface` follows both. A `Box` that receives a concrete `Monolog\Logger` uses injection, but still depends on one specific implementation. Monolog is simply one implementation of the PSR-3 `LoggerInterface` standard, and the interfaces in this project hide where a dependency comes from, so it can be replaced without changing the code that uses it.
