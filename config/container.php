<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use MEPDatabaseTask\database\ConnectionInterface;
use MEPDatabaseTask\database\PdoConnection;
use MEPDatabaseTask\services\DbalProductService;
use MEPDatabaseTask\services\ProductService;
use MEPDatabaseTask\services\ProductServiceInterface;
use MEPDatabaseTask\storage\Box;
use MEPDatabaseTask\storage\ExoticBox;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function DI\autowire;
use function DI\env;
use function DI\factory;
use function DI\get;

/*
 * PHP-DI definitions: how the services of the application are built.
 *
 * Classes that are not listed here are autowired: PHP-DI reads the type hints
 * of their constructor and injects the matching services. Every service is
 * created once per request and shared (the same object is returned each time).
 */
return [
    // --- Settings ------------------------------------------------------------
    'app.root' => dirname(__DIR__),
    'log.file' => env('LOG_FILE', 'logs/app.log'),
    'log.level' => env('LOG_LEVEL', 'debug'),

    // Database connection parameters, all from .env (loaded in config/bootstrap.php).
    'db.host' => env('DB_HOST'),
    'db.port' => env('DB_PORT', '3306'),
    'db.name' => env('DB_NAME'),
    'db.user' => env('DB_USER'),
    'db.password' => env('DB_PASSWORD', ''),
    'db.charset' => env('DB_CHARSET', 'utf8mb4'),

    // Which implementation the product pages use: "pdo" or "dbal" (Extra Mile).
    'db.layer' => env('DB_LAYER', 'pdo'),

    // --- Logging (PSR-3, implemented by Monolog) -----------------------------
    Logger::class => factory(static function (ContainerInterface $c): Logger {
        $logger = new Logger('app');

        // Fills PSR-3 placeholders: 'contains {count} pieces' + ['count' => 3].
        $logger->pushProcessor(new PsrLogMessageProcessor());

        $level = $c->get('log.level');

        // Always to stderr: the terminal for CLI scripts, `docker compose logs app` for php-fpm.
        $logger->pushHandler(new StreamHandler('php://stderr', $level));

        // And to a log file, as long as its directory is writable.
        $file = $c->get('log.file');
        if (!str_starts_with($file, '/')) {
            $file = $c->get('app.root') . '/' . $file;
        }
        $directory = dirname($file);
        if ((is_dir($directory) || @mkdir($directory, 0775, true)) && is_writable($directory)) {
            $logger->pushHandler(new StreamHandler($file, $level));
        }

        return $logger;
    }),

    // Application code depends on the PSR-3 interface only, never on Monolog.
    LoggerInterface::class => get(Logger::class),

    // The boxes log on their own "box" channel: same handlers, different name.
    'logger.box' => factory(static fn (Logger $logger): Logger => $logger->withName('box')),

    // --- Boxes ---------------------------------------------------------------
    // Box: created once and shared, so the whole application uses the same box.
    Box::class => autowire()
        ->constructorParameter('logger', get('logger.box')),

    // ExoticBox: use $container->make(ExoticBox::class, ['capacity' => 2]) for a new one.
    ExoticBox::class => autowire()
        ->constructorParameter('logger', get('logger.box')),

    // --- Database ------------------------------------------------------------
    // One PdoConnection per request, built from the .env settings above.
    PdoConnection::class => factory(static fn (ContainerInterface $c): PdoConnection => new PdoConnection(
        host: $c->get('db.host'),
        port: (int) $c->get('db.port'),
        database: $c->get('db.name'),
        username: $c->get('db.user'),
        password: $c->get('db.password'),
        charset: $c->get('db.charset'),
    )),

    // Whoever asks for a ConnectionInterface gets the PdoConnection.
    ConnectionInterface::class => get(PdoConnection::class),

    // Extra Mile: a Doctrine DBAL connection built from the same .env settings.
    // DBAL connects lazily, on the first query.
    Connection::class => factory(static fn (ContainerInterface $c): Connection => DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => $c->get('db.host'),
        'port' => (int) $c->get('db.port'),
        'dbname' => $c->get('db.name'),
        'user' => $c->get('db.user'),
        'password' => $c->get('db.password'),
        'charset' => $c->get('db.charset'),
    ])),

    // --- Products ------------------------------------------------------------
    // The DI container injects the connection into the ProductService.
    ProductService::class => autowire()
        ->constructorParameter('connection', get(ConnectionInterface::class)),

    // Extra Mile: the same operations through Doctrine DBAL.
    DbalProductService::class => autowire()
        ->constructorParameter('connection', get(Connection::class)),

    // The pages ask for the interface; DB_LAYER in .env decides what they get.
    ProductServiceInterface::class => factory(
        static fn (ContainerInterface $c): ProductServiceInterface => match ($c->get('db.layer')) {
            'pdo' => $c->get(ProductService::class),
            'dbal' => $c->get(DbalProductService::class),
            default => throw new InvalidArgumentException(
                'DB_LAYER must be "pdo" or "dbal", "' . $c->get('db.layer') . '" given.'
            ),
        }
    ),
];
