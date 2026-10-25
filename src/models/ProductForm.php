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
        if ($this->name === "" || $this->category === "") {
            return false;
        }

        if (mb_strlen($this->name) > 64 || mb_strlen($this->category) > 64) {
            return false;
        }

        if (mb_strlen($this->description) > 500) {
            return false;
        }

        return true;
    }
}
