<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage;

use MEPDatabaseTask\fruits\Colour;
use MEPDatabaseTask\fruits\Fruit;
use MEPDatabaseTask\storage\exception\BoxFullException;
use Psr\Log\LoggerInterface;

/**
 * Shared storage logic for every kind of box.
 *
 * Concrete boxes decide which fruits they accept; everything else lives here once.
 *
 * The PSR-3 logger is a constructor dependency: the DI container injects it
 * (see config/container.php), the box never creates or looks it up itself.
 */
abstract class AbstractBox implements BoxInterface
{
    public const DEFAULT_CAPACITY = 10;

    /** @var list<Fruit> */
    private array $fruits = [];

    private int $capacity;

    protected function __construct(
        private readonly LoggerInterface $logger,
        int $capacity = self::DEFAULT_CAPACITY
    ) {
        $this->setCapacity($capacity);
    }

    public function add(Fruit $fruit): void
    {
        $this->assertAccepts($fruit);

        if ($this->isFull()) {
            throw new BoxFullException($this->capacity);
        }

        $this->fruits[] = $fruit;
    }

    public function empty(): void
    {
        $this->fruits = [];
    }

    public function getFruits(): array
    {
        return $this->fruits;
    }

    /**
     * Every call is logged, e.g. "The box currently contains 3 pieces of fruits".
     */
    public function getCount(): int
    {
        $count = count($this->fruits);

        $this->logger->info('The box currently contains {count} pieces of fruits', [
            'count' => $count,
            'box' => static::class,
        ]);

        return $count;
    }

    public function getTotalCalories(): int
    {
        return array_sum(array_map(
            static fn (Fruit $fruit): int => $fruit->getCalorie(),
            $this->fruits
        ));
    }

    public function getFruitByColour(Colour $colour): array
    {
        return array_values(array_filter(
            $this->fruits,
            static fn (Fruit $fruit): bool => $fruit->getColour() === $colour
        ));
    }

    public function getFruitAt(int $index): ?Fruit
    {
        return $this->fruits[$index] ?? null;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * The limit can be changed at any time, but never below what is already in the box.
     */
    public function setCapacity(int $capacity): void
    {
        if ($capacity < 1) {
            throw new \InvalidArgumentException("Box capacity must be at least 1, {$capacity} given.");
        }

        // count() directly: internal checks are not calls to getCount() and are not logged.
        $current = count($this->fruits);
        if ($capacity < $current) {
            throw new \InvalidArgumentException(
                "Box capacity cannot be set to {$capacity}: the box already holds {$current} fruits."
            );
        }

        $this->capacity = $capacity;
    }

    public function isFull(): bool
    {
        return count($this->fruits) >= $this->capacity;
    }

    /**
     * Hook for boxes that only accept certain fruits.
     * Should throw FruitNotAllowedException to reject a fruit.
     */
    protected function assertAccepts(Fruit $fruit): void
    {
    }
}
