<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage\exception;

class BoxFullException extends \OverflowException
{
    public function __construct(private readonly int $capacity)
    {
        parent::__construct("The box is full! It cannot hold more than {$capacity} fruits.");
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }
}
