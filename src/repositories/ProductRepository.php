<?php

namespace MEPDatabaseTask\repositories;

use PDO;
use MEPDatabaseTask\models\Product;
/**
 * Class ProductRepository
 * @package MEPDatabaseTask\repositories
 *
 * Important note: Use PDO for Database connection
 * tip: use dependency injection and avoid using hard dependencies
 *
 */
class ProductRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function save(Product $product): void {
        $stmt = $this->db->prepare("
            INSERT INTO products (name, category, description, created_at) 
            VALUES (:name, :category, :description, NOW())
        ");
        
        $stmt->execute([
            ':name' => $product->name,
            ':category' => $product->category,
            ':description' => $product->description
        ]);
    }

    public function update(Product $product): void {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET name = :name, category = :category, description = :description, updated_at = NOW() 
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':name' => $product->name,
            ':category' => $product->category,
            ':description' => $product->description,
            ':id' => $product->id
        ]);
    }

    public function get(int $id): ?Product {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Product($row['name'], $row['category'], $row['description'], $row['id']);
    }

    public function getAll(int $page = 1): array {
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT * FROM products ORDER BY id ASC LIMIT :limit OFFSET :offset");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = new Product($row['name'], $row['category'], $row['description'], $row['id']);
        }

        return $products;
    }
}