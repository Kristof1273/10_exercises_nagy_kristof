<?php

declare(strict_types=1);

namespace MEPDatabaseTask\database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * MySQL connection through PDO.
 *
 * It knows nothing about where its settings come from: the DI container reads
 * them from .env and passes them to the constructor (see config/container.php).
 * The connection is opened on first use and then reused for the whole request.
 */
final class PdoConnection implements ConnectionInterface
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        #[\SensitiveParameter]
        private readonly string $password,
        private readonly string $charset = 'utf8mb4',
    ) {
    }

    public function getPdo(): PDO
    {
        return $this->pdo ??= $this->connect();
    }

    private function connect(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->charset
        );

        try {
            return new PDO($dsn, $this->username, $this->password, [
                // SQL errors become exceptions instead of silent false return values.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Real prepared statements on the MySQL server (values never become part of the SQL).
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // Re-thrown without the original exception on purpose: its stack trace
            // contains the PDO constructor arguments, including the password.
            throw new RuntimeException(sprintf(
                'Could not connect to the database "%s" at %s:%d: %s',
                $this->database,
                $this->host,
                $this->port,
                $e->getMessage()
            ));
        }
    }
}
