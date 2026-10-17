<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage;

use Psr\Log\LoggerInterface;


final class Box extends AbstractBox
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
}
