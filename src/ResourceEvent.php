<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Rest;

use ArrayAccess;
use InvalidArgumentException;
use Laminas\ApiTools\MvcAuth\Identity\IdentityInterface;
use Laminas\EventManager\Event;
use Laminas\EventManager\Exception\InvalidArgumentException as EventManagerInvalidArgumentException;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\Stdlib\RequestInterface;

use function gettype;
use function is_array;
use function is_object;
use function sprintf;

class ResourceEvent extends Event
{
    /** @var null|IdentityInterface */
    protected $identity;

    /** @var null|InputFilterInterface */
    protected $inputFilter;

    /** @var null|Parameters */
    protected $queryParams;

    /** @var null|RequestInterface */
    protected $request;

    /** @var null|RouteMatch */
    protected $routeMatch;

    /**
     * Overload setParams to inject request object, if passed via params
     *
     * @param array|ArrayAccess|object $params
     * @return self
     */
    public function setParams($params)
    {
        if (! is_array($params) && ! is_object($params)) {
            throw new EventManagerInvalidArgumentException(sprintf(
                'Event parameters must be an array or object; received "%s"',
                gettype($params)
            ));
        }

        if (is_array($params) || $params instanceof ArrayAccess) {
            if (isset($params['request'])) {
                $this->setRequest($params['request']);
                unset($params['request']);
            }
        }

        parent::setParams($params);
        return $this;
    }

    /**
     * @return self
     */
    public function setIdentity(?IdentityInterface $identity = null)
    {
        $this->identity = $identity;
        return $this;
    }

    /**
     * @return null|IdentityInterface
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return self
     */
    public function setInputFilter(?InputFilterInterface $inputFilter = null)
    {
        $this->inputFilter = $inputFilter;
        return $this;
    }

    /**
     * @return null|InputFilterInterface
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * @return self
     */
    public function setQueryParams(?Parameters $params = null)
    {
        $this->queryParams = $params;
        return $this;
    }

    /**
     * @return null|Parameters
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Retrieve a single query parameter by name
     *
     * If not present, returns the $default value provided.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryParam($name, $default = null)
    {
        $params = $this->getQueryParams();
        if (null === $params) {
            return $default;
        }

        return $params->get($name, $default);
    }

    /**
     * @return self
     */
    public function setRequest(?RequestInterface $request = null)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return null|RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RouteMatch $matches
     * @return self
     */
    public function setRouteMatch($matches = null)
    {
        if (null !== $matches && ! $matches instanceof RouteMatch) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a null or %s instances; received %s',
                __METHOD__,
                RouteMatch::class,
                is_object($matches) ? $matches::class : gettype($matches)
            ));
        }
        $this->routeMatch = $matches;
        return $this;
    }

    /**
     * @return null|RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Retrieve a single route match parameter by name.
     *
     * If not present, returns the $default value provided.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getRouteParam($name, $default = null)
    {
        $matches = $this->getRouteMatch();
        if (null === $matches) {
            return $default;
        }

        return $matches->getParam($name, $default);
    }
}
