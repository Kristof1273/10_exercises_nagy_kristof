<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage;

use MEPDatabaseTask\fruits\ExoticFruitInterface;
use MEPDatabaseTask\fruits\Fruit;
use MEPDatabaseTask\storage\exception\FruitNotAllowedException;
use Psr\Log\LoggerInterface;


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
