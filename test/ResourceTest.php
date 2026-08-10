<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Rest;

use ArrayIterator;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\Exception\InvalidArgumentException;
use Laminas\ApiTools\Rest\Resource;
use Laminas\ApiTools\Rest\ResourceEvent;
use Laminas\ApiTools\Rest\ResourceInterface;
use Laminas\EventManager\EventManager;
use Laminas\Http\Response;
use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\Parameters;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_map;
use function array_values;
use function call_user_func_array;

class ResourceTest extends TestCase
{
    use RouteMatchFactoryTrait;

    /** @var EventManager */
    private $events;

    /** @var Resource */
    private $resource;

    #[Override]
    public function setUp(): void
    {
        $this->events   = new EventManager();
        $this->resource = new Resource();
        $this->resource->setEventManager($this->events);
    }

    public function testEventManagerIdentifiersAreAsExpected(): void
    {
        $expected    = [
            Resource::class,
            ResourceInterface::class,
        ];
        $identifiers = $this->events->getIdentifiers();
        $this->assertEquals($expected, array_values($identifiers));
    }

    public static function badData(): array
    {
        return [
            'null'   => [null],
            'bool'   => [true],
            'int'    => [1],
            'float'  => [1.0],
            'string' => ['data'],
        ];
    }

    /**
     * @param mixed $data
     */
    #[DataProvider('badData')]
    public function testCreateRaisesExceptionWithInvalidData($data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resource->create($data);
    }

    public function testEventParamsReturnDefaultValueOnNonExistingParam(): void
    {
        $this->assertEquals('world', $this->resource->getEventParam('hello', 'world'));
    }

    public function testSameInstanceReturnedByEventParams(): void
    {
        $instance = new ArrayObject();

        $this->resource->setEventParam('instance', $instance);

        $this->assertEquals($instance, $this->resource->getEventParam('instance'));
    }

    public function testClearOldParamsOnSetEventParams(): void
    {
        $this->resource->setEventParam('world', 'hello');

        $params = ['hello' => 'world'];

        $this->resource->setEventParams($params);

        $this->assertEquals($params, $this->resource->getEventParams());
    }

    public function testCreateReturnsResultOfLastListener(): void
    {
        $this->events->attach('create', function ($e) {
            return null;
        });
        $object = new stdClass();
        $this->events->attach('create', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->create([]);
        $this->assertSame($object, $test);
    }

    public function testCreateReturnsDataIfLastListenerDoesNotReturnResource(): void
    {
        $data   = new stdClass();
        $object = new stdClass();
        $this->events->attach('create', function ($e) use ($object) {
            return $object;
        });
        $this->events->attach('create', function ($e) {
            return null;
        });

        $test = $this->resource->create($data);
        $this->assertSame($data, $test);
    }

    /**
     * @param mixed $data
     */
    #[DataProvider('badData')]
    public function testUpdateRaisesExceptionWithInvalidData($data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resource->update('foo', $data);
    }

    public function testUpdateReturnsResultOfLastListener(): void
    {
        $this->events->attach('update', function ($e) {
            return null;
        });
        $object = new stdClass();
        $this->events->attach('update', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->update('foo', []);
        $this->assertSame($object, $test);
    }

    public function testUpdateReturnsDataIfLastListenerDoesNotReturnResource(): void
    {
        $data   = new stdClass();
        $object = new stdClass();
        $this->events->attach('update', function ($e) use ($object) {
            return $object;
        });
        $this->events->attach('update', function ($e) {
            return null;
        });

        $test = $this->resource->update('foo', $data);
        $this->assertSame($data, $test);
    }

    /**
     * @param mixed $data
     */
    #[DataProvider('badUpdateCollectionData')]
    public function testReplaceListRaisesExceptionWithInvalidData($data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data');
        $this->expectExceptionCode(400);

        $this->resource->replaceList($data);
    }

    public function testReplaceListReturnsResultOfLastListener(): void
    {
        $this->events->attach('replaceList', function ($e) {
            return null;
        });
        $object = [new stdClass()];
        $this->events->attach('replaceList', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->replaceList([[]]);
        $this->assertSame($object, $test);
    }

    public function testReplaceListReturnsDataIfLastListenerDoesNotReturnResource(): void
    {
        $data   = [new stdClass()];
        $object = new stdClass();
        $this->events->attach('replaceList', function ($e) use ($object) {
            return $object;
        });
        $this->events->attach('replaceList', function ($e) {
            return null;
        });

        $test = $this->resource->replaceList($data);
        $this->assertSame($data, $test);
    }

    /**
     * @param mixed $data
     */
    #[DataProvider('badData')]
    public function testPatchRaisesExceptionWithInvalidData($data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resource->patch('foo', $data);
    }

    public function testPatchReturnsResultOfLastListener(): void
    {
        $this->events->attach('patch', function ($e) {
            return null;
        });
        $object = new stdClass();
        $this->events->attach('patch', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->patch('foo', []);
        $this->assertSame($object, $test);
    }

    public function testPatchReturnsDataIfLastListenerDoesNotReturnResource(): void
    {
        $data   = new stdClass();
        $object = new stdClass();
        $this->events->attach('patch', function ($e) use ($object) {
            return $object;
        });
        $this->events->attach('patch', function ($e) {
            return null;
        });

        $test = $this->resource->patch('foo', $data);
        $this->assertSame($data, $test);
    }

    public function testDeleteReturnsResultOfLastListenerIfBoolean(): void
    {
        $this->events->attach('delete', function ($e) {
            return new stdClass();
        });
        $this->events->attach('delete', function ($e) {
            return true;
        });

        $test = $this->resource->delete('foo', []);
        $this->assertTrue($test);
    }

    public function testDeleteReturnsFalseIfLastListenerDoesNotReturnBoolean(): void
    {
        $this->events->attach('delete', function ($e) {
            return true;
        });
        $this->events->attach('delete', function ($e) {
            return new stdClass();
        });

        $test = $this->resource->delete('foo');
        $this->assertFalse($test);
    }

    public static function badDeleteCollections(): array
    {
        return [
            'true'     => [true],
            'int'      => [1],
            'float'    => [1.1],
            'string'   => ['string'],
            'stdClass' => [new stdClass()],
        ];
    }

    /**
     * @param mixed $data
     */
    #[DataProvider('badDeleteCollections')]
    public function testDeleteListRaisesInvalidArgumentExceptionForInvalidData($data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('::deleteList');
        $this->resource->deleteList($data);
    }

    public function testDeleteListReturnsResultOfLastListenerIfBoolean(): void
    {
        $this->events->attach('deleteList', function ($e) {
            return new stdClass();
        });
        $this->events->attach('deleteList', function ($e) {
            return true;
        });

        $test = $this->resource->deleteList([]);
        $this->assertTrue($test);
    }

    public function testDeleteListReturnsFalseIfLastListenerDoesNotReturnBoolean(): void
    {
        $this->events->attach('deleteList', function ($e) {
            return true;
        });
        $this->events->attach('deleteList', function ($e) {
            return new stdClass();
        });

        $test = $this->resource->deleteList([]);
        $this->assertFalse($test);
    }

    public function testFetchReturnsResultOfLastListener(): void
    {
        $this->events->attach('fetch', function ($e) {
            return true;
        });
        $object = new stdClass();
        $this->events->attach('fetch', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->fetch('foo');
        $this->assertSame($object, $test);
    }

    /**
     * @param mixed $return
     */
    #[DataProvider('badData')]
    public function testFetchReturnsFalseIfLastListenerDoesNotReturnArrayOrObject($return): void
    {
        $this->events->attach('fetch', function ($e) use ($return) {
            return $return;
        });
        $test = $this->resource->fetch('foo');
        $this->assertFalse($test);
    }

    public static function invalidCollection(): array
    {
        return [
            'null'   => [null],
            'bool'   => [true],
            'int'    => [1],
            'float'  => [1.0],
            'string' => ['data'],
        ];
    }

    /**
     * @param mixed $return
     */
    #[Group('31')]
    #[DataProvider('invalidCollection')]
    public function testFetchAllReturnsEmptyArrayIfLastListenerReturnsScalar($return): void
    {
        $this->events->attach('fetchAll', function ($e) use ($return) {
            return $return;
        });
        $test = $this->resource->fetchAll();
        $this->assertEquals([], $test);
    }

    public function testFetchAllReturnsResultOfLastListener(): void
    {
        $this->events->attach('fetchAll', function ($e) {
            return true;
        });
        $object = new ArrayIterator([]);
        $this->events->attach('fetchAll', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->fetchAll();
        $this->assertSame($object, $test);
    }

    public static function eventsToTrigger(): array
    {
        $id = 'resource_id';

        $resource = [
            'id'  => $id,
            'foo' => 'foo',
            'bar' => 'bar',
        ];

        $collection = [$resource];

        return [
            'create'      => ['create', [$resource], false],
            'update'      => ['update', [$id, $resource], true],
            'replaceList' => ['replaceList', [$collection], false],
            'patch'       => ['patch', [$id, $resource], true],
            'patchList'   => ['patchList', [$collection], false],
            'delete'      => ['delete', [$id], true],
            'deleteList'  => ['deleteList', [$collection], false],
            'fetch'       => ['fetch', [$id], true],
            'fetchAll'    => ['fetchAll', [], false],
        ];
    }

    /**
     * eventsToTrigger without the id-is-present column.
     *
     * @return array<string, array{0: string, 1: array<mixed>}>
     */
    public static function eventsToTriggerWithoutIdFlag(): array
    {
        return array_map(
            static fn (array $set): array => [$set[0], $set[1]],
            self::eventsToTrigger()
        );
    }

    /**
     * @param string $eventName
     */
    #[DataProvider('eventsToTriggerWithoutIdFlag')]
    public function testEventTerminateIfApiProblemIsReturned($eventName, array $args): void
    {
        $called = false;

        $this->events->attach($eventName, function () {
            return new ApiProblem(400, 'Random error');
        }, 10);

        $this->events->attach($eventName, function () use (&$called) {
            $called = true;
        }, 0);

        call_user_func_array([$this->resource, $eventName], $args);

        $this->assertFalse($called);
    }

    /**
     * @param string $eventName
     * @param bool $idIsPresent
     */
    #[DataProvider('eventsToTrigger')]
    public function testEventParametersAreInjectedIntoEventWhenTriggered($eventName, array $args, $idIsPresent): void
    {
        $test = (object) [];
        $this->events->attach($eventName, function ($e) use ($test) {
            $test->event = $e;
        });
        $this->resource->setEventParam('id', 'OVERWRITTEN');
        $this->resource->setEventParam('parent_id', 'parent_id');

        call_user_func_array([$this->resource, $eventName], $args);

        $this->assertObjectHasProperty('event', $test);
        $e = $test->event;

        if ($idIsPresent) {
            $this->assertNotFalse($e->getParam('id', false));
            $this->assertNotEquals('OVERWRITTEN', $e->getParam('id'));
        }

        $this->assertNotFalse($e->getParam('parent_id', false));
        $this->assertEquals('parent_id', $e->getParam('parent_id'));
    }

    /**
     * @param string $eventName
     */
    #[DataProvider('eventsToTriggerWithoutIdFlag')]
    public function testComposedQueryParametersAndRouteMatchesAreInjectedIntoEvent($eventName, array $args): void
    {
        $test = (object) [];
        $this->events->attach($eventName, function ($e) use ($test) {
            $test->event = $e;
        });
        $matches     = $this->createRouteMatch([]);
        $queryParams = new Parameters();
        $this->resource->setRouteMatch($matches);
        $this->resource->setQueryParams($queryParams);

        call_user_func_array([$this->resource, $eventName], $args);

        $this->assertObjectHasProperty('event', $test);
        $e = $test->event;

        $this->assertInstanceOf(ResourceEvent::class, $e);
        $this->assertSame($matches, $e->getRouteMatch());
        $this->assertSame($queryParams, $e->getQueryParams());
    }

    public static function badUpdateCollectionData(): array
    {
        return [
            'object'    => [new stdClass()],
            'notnested' => [[null]],
        ];
    }

    /**
     * @param mixed $data
     */
    #[DataProvider('badData')]
    #[DataProvider('badUpdateCollectionData')]
    public function testPatchListListRaisesExceptionWithInvalidData($data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data');
        $this->expectExceptionCode(400);

        $this->resource->patchList($data);
    }

    public function testPatchListReturnsResultOfLastListener(): void
    {
        $this->events->attach('patchList', function ($e) {
            return null;
        });
        $object = [new stdClass()];
        $this->events->attach('patchList', function ($e) use ($object) {
            return $object;
        });

        $test = $this->resource->patchList([[]]);
        $this->assertSame($object, $test);
    }

    public function testPatchListReturnsDataIfLastListenerDoesNotReturnResource(): void
    {
        $data   = [new stdClass()];
        $object = new stdClass();
        $this->events->attach('patchList', function ($e) use ($object) {
            return $object;
        });
        $this->events->attach('patchList', function ($e) {
            return null;
        });

        $test = $this->resource->patchList($data);
        $this->assertSame($data, $test);
    }

    #[Group('31')]
    public function testFetchAllShouldAllowReturningArbitraryObjects(): void
    {
        $return = (object) ['foo' => 'bar'];
        $this->events->attach('fetchAll', function ($e) use ($return) {
            return $return;
        });
        $test = $this->resource->fetchAll();
        $this->assertSame($return, $test);
    }

    public static function actions(): array
    {
        return [
            'get-list'    => ['fetchAll', [null]],
            'get'         => ['fetch', [1]],
            'post'        => ['create', [[]]],
            'put-list'    => ['replaceList', [[]]],
            'put'         => ['update', [1, []]],
            'patch-list'  => ['patchList', [[]]],
            'patch'       => ['patch', [1, []]],
            'delete-list' => ['deleteList', [[]]],
            'delete'      => ['delete', [1]],
        ];
    }

    /**
     * @param string $action
     */
    #[Group('68')]
    #[DataProvider('actions')]
    public function testAllowsReturningResponsesReturnedFromResources($action, array $argv): void
    {
        $response = new Response();
        $response->setStatusCode(418);

        $events = $this->resource->getEventManager();
        $events->attach($action, function ($e) use ($response) {
            return $response;
        });

        $result = call_user_func_array([$this->resource, $action], $argv);
        $this->assertSame($response, $result);
    }
}
