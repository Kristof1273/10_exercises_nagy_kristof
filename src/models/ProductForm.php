<?php

namespace MEPDatabaseTask\models;

class ProductForm
{
    public string $name;
    public string $category;
    public string $description;
    public ?int $id;

    public function __construct(string $name = '', string $category = '', string $description = '', ?int $id = null) {
        $this->name = trim($name);
        $this->category = trim($category);
        $this->description = trim($description);
        $this->id = $id;
    }

    public function validate(): bool {
        if (empty($this->name) || empty($this->category)) {
            return false;
        }

        if (strlen($this->name) > 64 || strlen($this->category) > 64) {
            return false;
        }

        if (strlen($this->description) > 500) {
            return false;
        }

        return true;
    }
}
