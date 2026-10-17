<?php
declare(strict_types=1);

namespace MEPDatabaseTask\fruits;

class Mango extends Fruit implements ExoticFruitInterface, PeelableInterface
{
    use PeelableTrait;
}
