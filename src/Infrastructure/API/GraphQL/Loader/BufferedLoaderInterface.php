<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

interface BufferedLoaderInterface
{
    public function init(): void;
}
