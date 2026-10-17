<?php

declare(strict_types=1);

namespace MEPDatabaseTask\services;

use InvalidArgumentException;
use MEPDatabaseTask\database\ConnectionInterface;
use MEPDatabaseTask\models\Product;
use PDO;


final class ProductService implements ProductServiceInterface
{
    private const COLUMNS = 'id, name, category, description, created_at, updated_at';

    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    public function save(Product $product): int
    {
        $pdo = $this->connection->getPdo();
        $statement = $pdo->prepare(
            'INSERT INTO products (name, category, description, created_at)
             VALUES (:name, :category, :description, CURRENT_TIMESTAMP)'
        );
        $statement->execute([
            ':name' => $product->name,
            ':category' => $product->category,
            ':description' => $product->description,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function update(Product $product): void
    {
        if ($product->id === null) {
            throw new InvalidArgumentException('Only a product with an ID can be updated.');
        }

        $statement = $this->connection->getPdo()->prepare(
            'UPDATE products
             SET name = :name, category = :category, description = :description, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->bindValue(':name', $product->name);
        $statement->bindValue(':category', $product->category);
        $statement->bindValue(':description', $product->description);
        $statement->bindValue(':id', $product->id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function get(int $id): ?Product
    {
        $statement = $this->connection->getPdo()->prepare(
            'SELECT ' . self::COLUMNS . ' FROM products WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : Product::fromRow($row);
    }

    public function getAll(int $page = 1): array
    {
        $page = max(1, $page);

        $statement = $this->connection->getPdo()->prepare(
            'SELECT ' . self::COLUMNS . ' FROM products ORDER BY id LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', self::PAGE_SIZE, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * self::PAGE_SIZE, PDO::PARAM_INT);
        $statement->execute();

        return array_map(
            static fn (array $row): Product => Product::fromRow($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function count(): int
    {
        return (int) $this->connection->getPdo()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }
}
