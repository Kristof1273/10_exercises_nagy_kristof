<?php

declare(strict_types=1);

namespace MEPDatabaseTask\services;

use MEPDatabaseTask\models\Product;

/**
 * Every operation the product pages need.
 *
 * The pages only know this interface; config/container.php decides which
 * implementation they get.
 */
interface ProductServiceInterface
{
    /** Number of products on one page of getAll(). */
    public const PAGE_SIZE = 10;

    /** Inserts a new product and returns its ID. */
    public function save(Product $product): int;

    /** Updates the product identified by $product->id and sets its updated_at. */
    public function update(Product $product): void;

    /** The product with this ID, or null if there is none. */
    public function get(int $id): ?Product;

    /**
     * One page of products ordered by ID: page 1 = products 1–10, page 2 = 11–20, ...
     *
     * @return list<Product>
     */
    public function getAll(int $page = 1): array;

    /** Total number of products (for the pagination). */
    public function count(): int;
}
