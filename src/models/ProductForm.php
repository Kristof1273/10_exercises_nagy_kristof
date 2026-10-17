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

        // mb_strlen counts characters like VARCHAR(64) does; strlen would count
        // bytes, so a name with accented letters (2 bytes each) failed too early.
        if (mb_strlen($this->name, 'UTF-8') > 64 || mb_strlen($this->category, 'UTF-8') > 64) {
            return false;
        }

        if (mb_strlen($this->description, 'UTF-8') > 500) {
            return false;
        }

        return true;
    }
}
