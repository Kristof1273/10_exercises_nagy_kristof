<?php
declare(strict_types=1);

namespace MEPDatabaseTask\fruits;

/**
 * Default implementation of PeelableInterface, shared by every peelable fruit.
 */
trait PeelableTrait
{
    private bool $peeled = false;

    public function peel(): void { $this->peeled = true; }
    public function isPeeled(): bool { return $this->peeled; }
}
