<?php

declare(strict_types=1);

namespace MEPDatabaseTask\services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use InvalidArgumentException;
use MEPDatabaseTask\models\Product;

/**
 * Same operations as ProductService, implemented with Doctrine DBAL (Extra Mile).
 *
 * DBAL manages the connection (built from .env in config/container.php) and
 * its QueryBuilder writes the SQL; values are always bound as parameters.
 * Choose it with DB_LAYER=dbal in .env — the pages do not change at all,
 * because they only depend on ProductServiceInterface.
 */
final class DbalProductService implements ProductServiceInterface
{
    private const COLUMNS = ['id', 'name', 'category', 'description', 'created_at', 'updated_at'];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function save(Product $product): int
    {
        $this->connection->createQueryBuilder()
            ->insert('products')
            ->values([
                'name' => ':name',
                'category' => ':category',
                'description' => ':description',
                'created_at' => 'CURRENT_TIMESTAMP',
            ])
            ->setParameter('name', $product->name)
            ->setParameter('category', $product->category)
            ->setParameter('description', $product->description)
            ->executeStatement();

        return (int) $this->connection->lastInsertId();
    }

    public function update(Product $product): void
    {
        if ($product->id === null) {
            throw new InvalidArgumentException('Only a product with an ID can be updated.');
        }

        $this->connection->createQueryBuilder()
            ->update('products')
            ->set('name', ':name')
            ->set('category', ':category')
            ->set('description', ':description')
            ->set('updated_at', 'CURRENT_TIMESTAMP')
            ->where('id = :id')
            ->setParameter('name', $product->name)
            ->setParameter('category', $product->category)
            ->setParameter('description', $product->description)
            ->setParameter('id', $product->id, ParameterType::INTEGER)
            ->executeStatement();
    }

    public function get(int $id): ?Product
    {
        $row = $this->connection->createQueryBuilder()
            ->select(...self::COLUMNS)
            ->from('products')
            ->where('id = :id')
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : Product::fromRow($row);
    }

    public function getAll(int $page = 1): array
    {
        $page = max(1, $page);

        $rows = $this->connection->createQueryBuilder()
            ->select(...self::COLUMNS)
            ->from('products')
            ->orderBy('id')
            ->setFirstResult(($page - 1) * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn (array $row): Product => Product::fromRow($row), $rows);
    }

    public function count(): int
    {
        return (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('products')
            ->executeQuery()
            ->fetchOne();
    }
}
