<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Rest\TestAsset;

use JsonSerializable as JsonSerializableInterface;
use Override;

class JsonSerializable implements JsonSerializableInterface
{
    /** @return array */
    #[Override]
    public function jsonSerialize(): mixed
    {
        return ['foo' => 'bar'];
    }
}
