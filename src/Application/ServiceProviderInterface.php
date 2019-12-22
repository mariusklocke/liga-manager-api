<?php declare(strict_types=1);

namespace HexagonalPlayground\Application;

interface ServiceProviderInterface
{
    public function getDefinitions(): array;
}