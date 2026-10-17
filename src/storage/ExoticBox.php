<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage;

use MEPDatabaseTask\fruits\ExoticFruitInterface;
use MEPDatabaseTask\fruits\Fruit;
use MEPDatabaseTask\storage\exception\FruitNotAllowedException;
use Psr\Log\LoggerInterface;

/**
 * A box that only accepts exotic fruits (anything implementing ExoticFruitInterface).
 *
 * It is a sibling of Box, not a child of it, so it is independent of the shared Box.
 */
class ExoticBox extends AbstractBox
{
    public function __construct(LoggerInterface $logger, int $capacity = self::DEFAULT_CAPACITY)
    {
        parent::__construct($logger, $capacity);
    }

    protected function assertAccepts(Fruit $fruit): void
    {
        if (!$fruit instanceof ExoticFruitInterface) {
            throw new FruitNotAllowedException(
                "This box only accepts exotic fruits, {$fruit->getType()} is not exotic."
            );
        }
    }
}
