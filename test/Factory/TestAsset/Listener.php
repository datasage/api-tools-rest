<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Rest\Factory\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Override;

class Listener implements ListenerAggregateInterface
{
    /** @param int $priority */
    #[Override]
    public function attach(EventManagerInterface $events, $priority = 1)
    {
    }

    #[Override]
    public function detach(EventManagerInterface $events)
    {
    }
}
