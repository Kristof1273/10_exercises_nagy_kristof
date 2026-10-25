<?php

namespace MEPDatabaseTask\models;

class Product
{
    public string $name;
    public string $category;
    public string $description;
    public ?int $id;
    public ?string $createdAt;
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
}
