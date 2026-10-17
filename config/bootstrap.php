<?php

declare(strict_types=1);

/*
 * Shared bootstrap for every entry point (pages, box.php, db_init.php):
 * loads the Composer autoloader and the .env file, then builds the PHP-DI
 * container.
 *
 *     $container = require __DIR__ . '/config/bootstrap.php';
 */

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

// Values from .env end up in $_ENV, where DI\env() in config/container.php reads them.
// "Immutable": variables that are already set (e.g. by Docker) are not overwritten.
$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

try {
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
    $dotenv->required('DB_PORT')->isInteger();
    $dotenv->required('DB_PASSWORD');
    $dotenv->ifPresent('DB_LAYER')->allowedValues(['pdo', 'dbal']);
} catch (ValidationException $e) {
    throw new RuntimeException(
        'Missing or invalid configuration. Copy .env.example to .env and fill it in. (' . $e->getMessage() . ')'
    );
}

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/container.php');

return $builder->build();
