<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage;

use MEPDatabaseTask\fruits\Colour;
use MEPDatabaseTask\fruits\Fruit;
use MEPDatabaseTask\storage\exception\BoxFullException;
use MEPDatabaseTask\storage\exception\FruitNotAllowedException;

interface BoxInterface
{
    /**
     * @throws BoxFullException         when the box has reached its capacity
     * @throws FruitNotAllowedException when the box does not accept this kind of fruit
     */
    public function add(Fruit $fruit): void;

    public function empty(): void;

    /** @return list<Fruit> */
    public function getFruits(): array;

    public function getCount(): int;

    public function getTotalCalories(): int;

    /** @return list<Fruit> */
    public function getFruitByColour(Colour $colour): array;

    public function getFruitAt(int $index): ?Fruit;
}
