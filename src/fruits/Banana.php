<?php
declare(strict_types=1);

namespace MEPDatabaseTask\fruits;

class Banana extends Fruit implements PeelableInterface
{
    use PeelableTrait;
}
