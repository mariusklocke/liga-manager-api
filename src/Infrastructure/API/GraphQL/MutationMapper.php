<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Domain\Util\StringUtils;

class MutationMapper
{
    /** @var TypeMapper */
    private $typeMapper;

    public function __construct()
    {
        $this->typeMapper = new TypeMapper();
    }

    public function getDefinition(string $commandClass): array
    {
        $name = StringUtils::stripNamespace($commandClass);
        $name = str_replace('Command', '', $name);
        $name = lcfirst($name);

        $argTypes      = $this->buildArgumentTypes($commandClass);
        $argumentOrder = array_keys($argTypes);

        return [
            $name => [
                'args' => $argTypes,
                'type' => Type::boolean(),
                'resolve' => function ($val, $args, AppContext $context) use ($commandClass, $argumentOrder) {
                    $command = $this->createCommand($commandClass, $argumentOrder, $args);
                    if (method_exists($command, 'withAuthenticatedUser')) {
                        $command = $command->withAuthenticatedUser($context->getAuthenticatedUser());
                    }
                    if (method_exists($command, 'withBaseUri')) {
                        $command = $command->withBaseUri($context->getRequest()->getUri());
                    }

                    /** @var CommandBus $commandBus */
                    $commandBus = $context->getContainer()->get('commandBus');
                    $commandBus->execute($command);
                    return true;
                }
            ]
        ];
    }

    private function buildArgumentTypes(string $commandClass): array
    {
        return array_map(function(string $internalType) {
            return $this->typeMapper->map($internalType);
        }, $this->parseConstructorArgs($commandClass));
    }

    private function createCommand(string $commandClass, array $argumentOrder, array $argValues): CommandInterface
    {
        $finalArgs = [];
        foreach ($argumentOrder as $index => $name) {
            if (isset($argValues[$name])) {
                $finalArgs[$index] = $argValues[$name];
            }
        }
        return new $commandClass(...$finalArgs);
    }

    private function parseConstructorArgs(string $commandClass): array
    {
        $constructorDocBlock = (new \ReflectionClass($commandClass))->getConstructor()->getDocComment();
        if (!is_string($constructorDocBlock)) {
            throw new MappingException('Missing DocBlock for ' . $commandClass);
        }

        $args = [];
        foreach (explode("\n", $constructorDocBlock) as $line) {
            $matches = [];
            if (preg_match('/@param\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $name = substr($matches[2], 1); // strip $ character
                $name = StringUtils::camelCaseToSeparatedLowercase($name, '_');
                $args[$name] = $matches[1];
            }
        }

        return $args;
    }
}