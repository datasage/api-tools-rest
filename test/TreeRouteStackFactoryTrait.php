<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Rest;

use Laminas\Router\Http\TreeRouteStack;

trait TreeRouteStackFactoryTrait
{
    /**
     * Create and return a version-specific TreeRouteStack instance.
     *
     * @return TreeRouteStack
     */
    public function createTreeRouteStack()
    {
        return new TreeRouteStack();
    }
}
