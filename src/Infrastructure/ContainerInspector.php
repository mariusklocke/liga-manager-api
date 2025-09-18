<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Psr\Container\ContainerInterface;
use ReflectionClass;

class ContainerInspector
{
    public function inspect(ContainerInterface $container): array
    {
        $reflectionClass  = new ReflectionClass($container);
        $definitionSource = $reflectionClass->getProperty('definitionSource')->getValue($container);

        $result = [];
        foreach ($definitionSource->getDefinitions() as $key => $definition) {
            $result[$key] = get_class($definition);
        }

        return $result;
    }
}
