<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Rest;

use Laminas\Router\RouteMatch;

trait RouteMatchFactoryTrait
{
    /**
     * Create and return a version-specific RouteMatch instance.
     *
     * @return RouteMatch
     */
    public function createRouteMatch(array $params = [])
    {
        $class = $this->getRouteMatchClass();
        return new $class($params);
    }

    /**
     * Return a version-specific route match class.
     *
     * @return class-string
     */
    public function getRouteMatchClass()
    {
        return RouteMatch::class;
    }
}
