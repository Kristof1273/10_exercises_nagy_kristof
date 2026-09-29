<?php

namespace MEPDatabaseTask\models;

class Product
{
    public string $name;
    public string $category;
    public string $description;
    public ?int $id;

    public function __construct(string $name, string $category, string $description, ?int $id = null) {
        $this->name = $name;
        $this->category = $category;
        $this->description = $description;
        $this->id = $id;
    }
}
