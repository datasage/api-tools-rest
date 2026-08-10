<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Rest;

use Laminas\ApiTools\MvcAuth\Identity\GuestIdentity;
use Laminas\ApiTools\Rest\ResourceEvent;
use Laminas\Http\Request as HttpRequest;
use Laminas\InputFilter\InputFilter;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\Parameters;
use Override;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class ResourceEventTest extends TestCase
{
    use RouteMatchFactoryTrait;

    /** @var RouteMatch|V2RouteMatch */
    private $matches;

    /** @var Parameters */
    private $query;

    /** @var ResourceEvent */
    private $event;

    #[Override]
    public function setUp(): void
    {
        $this->matches = $this->createRouteMatch([
            'foo' => 'bar',
            'baz' => 'inga',
        ]);
        $this->query   = new Parameters([
            'foo' => 'bar',
            'baz' => 'inga',
        ]);

        $this->event = new ResourceEvent();
    }

    public function testRouteMatchIsNullByDefault(): void
    {
        $this->assertNull($this->event->getRouteMatch());
    }

    public function testQueryParamsAreNullByDefault(): void
    {
        $this->assertNull($this->event->getQueryParams());
    }

    public function testRouteMatchIsMutable(): ResourceEvent
    {
        $this->event->setRouteMatch($this->matches);
        $this->assertSame($this->matches, $this->event->getRouteMatch());
        return $this->event;
    }

    public function testQueryParamsAreMutable(): ResourceEvent
    {
        $this->event->setQueryParams($this->query);
        $this->assertSame($this->query, $this->event->getQueryParams());
        return $this->event;
    }

    public function testRequestIsNullByDefault(): void
    {
        $this->assertNull($this->event->getRequest());
    }

    public function testRequestIsMutable(): ResourceEvent
    {
        $request = new HttpRequest();
        $this->event->setRequest($request);
        $this->assertSame($request, $this->event->getRequest());
        return $this->event;
    }

    #[Depends('testRouteMatchIsMutable')]
    public function testRouteMatchIsNullable(ResourceEvent $event): void
    {
        $event->setRouteMatch(null);
        $this->assertNull($event->getRouteMatch());
    }

    #[Depends('testQueryParamsAreMutable')]
    public function testQueryParamsAreNullable(ResourceEvent $event): void
    {
        $event->setQueryParams(null);
        $this->assertNull($event->getQueryParams());
    }

    #[Depends('testRequestIsMutable')]
    public function testRequestIsNullable(ResourceEvent $event): void
    {
        $event->setRequest(null);
        $this->assertNull($event->getRequest());
    }

    public function testCanInjectRequestViaSetParams(): void
    {
        $request = new HttpRequest();
        $this->event->setParams(['request' => $request]);
        $this->assertSame($request, $this->event->getRequest());
    }

    public function testCanFetchIndividualRouteParameter(): void
    {
        $this->event->setRouteMatch($this->matches);
        $this->assertEquals('bar', $this->event->getRouteParam('foo'));
        $this->assertEquals('inga', $this->event->getRouteParam('baz'));
    }

    public function testCanFetchIndividualQueryParameter(): void
    {
        $this->event->setQueryParams($this->query);
        $this->assertEquals('bar', $this->event->getQueryParam('foo'));
        $this->assertEquals('inga', $this->event->getQueryParam('baz'));
    }

    public function testReturnsDefaultParameterWhenPullingUnknownRouteParameter(): void
    {
        $this->assertNull($this->event->getRouteParam('foo'));
        $this->assertEquals('bat', $this->event->getRouteParam('baz', 'bat'));
    }

    public function testReturnsDefaultParameterWhenPullingUnknownQueryParameter(): void
    {
        $this->assertNull($this->event->getQueryParam('foo'));
        $this->assertEquals('bat', $this->event->getQueryParam('baz', 'bat'));
    }

    public function testInputFilterIsUndefinedByDefault(): void
    {
        $this->assertNull($this->event->getInputFilter());
    }

    #[Depends('testInputFilterIsUndefinedByDefault')]
    public function testCanComposeInputFilter(): void
    {
        $inputFilter = new InputFilter();
        $this->event->setInputFilter($inputFilter);
        $this->assertSame($inputFilter, $this->event->getInputFilter());
    }

    #[Depends('testCanComposeInputFilter')]
    public function testCanNullifyInputFilter(): void
    {
        $this->event->setInputFilter(null);
        $this->assertNull($this->event->getInputFilter());
    }

    public function testIdentityIsUndefinedByDefault(): void
    {
        $this->assertNull($this->event->getIdentity());
    }

    #[Depends('testIdentityIsUndefinedByDefault')]
    public function testCanComposeIdentity(): void
    {
        $identity = new GuestIdentity();
        $this->event->setIdentity($identity);
        $this->assertSame($identity, $this->event->getIdentity());
    }

    #[Depends('testCanComposeIdentity')]
    public function testCanNullifyIdentity(): void
    {
        $this->event->setIdentity(null);
        $this->assertNull($this->event->getIdentity());
    }
}
