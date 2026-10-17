<?php

namespace MEPDatabaseTask\models;

class Product
{
    public string $name;
    public string $category;
    public string $description;
    public ?int $id;
    /** Set when the product is loaded from the database ("Y-m-d H:i:s"). */
    public ?string $createdAt;
    /** Null until the product is updated for the first time. */
    public ?string $updatedAt;

    public function __construct(
        string $name,
        string $category,
        string $description,
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->name = $name;
        $this->category = $category;
        $this->description = $description;
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Builds a Product from a row of the products table.
     *
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (string) $row['name'],
            (string) $row['category'],
            (string) ($row['description'] ?? ''), // description is optional (NULL in the database)
            (int) $row['id'],
            $row['created_at'] !== null ? (string) $row['created_at'] : null,
            $row['updated_at'] !== null ? (string) $row['updated_at'] : null
        );
    }
}
