<?php
declare(strict_types=1);

namespace MEPDatabaseTask\fruits;

abstract class Fruit
{
    public function __construct(
        protected readonly Size $size,
        protected readonly Colour $colour,
        protected readonly int $calories
    ) {
        if ($calories < 0) {
            throw new \InvalidArgumentException("Calories cannot be negative, {$calories} given.");
        }
    }

    public function getSize(): Size { return $this->size; }
    public function getColour(): Colour { return $this->colour; }
    public function getCalorie(): int { return $this->calories; }

    public function getType(): string { return static::class; }
}
