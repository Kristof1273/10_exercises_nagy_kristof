<?php
declare(strict_types=1);

namespace MEPDatabaseTask\storage;

use Psr\Log\LoggerInterface;

/**
 * General purpose box.
 *
 * There is still only one Box in the application, but the DI container takes
 * care of that now: PHP-DI creates the Box once and hands the same instance to
 * everyone who asks for it. The former getInstance() singleton had a private
 * constructor, so nothing could pass dependencies (like the logger) into it.
 */
final class Box extends AbstractBox
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
}
