<?php

declare(strict_types=1);

namespace MEPDatabaseTask\database;

use PDO;

/**
 * Hands out a ready-to-use PDO connection.
 *
 * Services depend on this interface instead of PdoConnection, so the actual
 * connection (another database, a test database, ...) is chosen in one place:
 * config/container.php.
 */
interface ConnectionInterface
{
    public function getPdo(): PDO; // DIP
}
