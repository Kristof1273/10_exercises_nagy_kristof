<?php
declare(strict_types=1);

namespace MEPDatabaseTask\fruits;

interface PeelableInterface {
    public function peel(): void;
    public function isPeeled(): bool;
}
