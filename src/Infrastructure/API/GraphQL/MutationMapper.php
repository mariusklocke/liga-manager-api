<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Domain\Util\StringUtils;
use Psr\Container\ContainerInterface;

class MutationMapper
{
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
                'resolve' => function ($val, $args, ContainerInterface $container) use ($commandClass, $argumentOrder) {
                    /** @var CommandBus $commandBus */
                    $commandBus = $container->get('commandBus');
                    $command    = $this->createCommand($commandClass, $argumentOrder, $args);
                    $commandBus->execute($command->withAuthenticatedUser(new User('foo@example.com', '123456', 'foo', 'bar', 'admin')));
                    return true;
                }
            ]
        ];
    }

    private function buildArgumentTypes(string $commandClass): array
    {
        return array_map(function(string $internalType) {
            return $this->mapType($internalType);
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
            throw new \Exception('Missing DocBlock for ' . $commandClass);
        }
        $args = [];
        foreach (explode("\n", $constructorDocBlock) as $line) {
            $matches = [];
            if (preg_match('/@param\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $name = substr($matches[2], 1); // strip $ characters
                $args[$name] = $matches[1];
            }
        }

        return $args;
    }

    private function mapType(string $internalType): Type
    {
        switch ($internalType) {
            case 'string':
                return Type::string();
            case 'string[]':
                return Type::listOf(Type::string());
            case 'int':
            case 'integer':
                return Type::int();
            case 'DatePeriod':
                return DatePeriodType::getInstance();
            case 'DatePeriod[]':
                return Type::listOf(DatePeriodType::getInstance());
            case 'float|int':
                return Type::float();
            case 'UriInterface':
                return Type::string();
            case 'TeamIdPair[]':
                return Type::listOf(Type::string());
            case 'string|null':
                return Type::string();
            case 'string[]|null':
                return Type::listOf(Type::string());
        }

        throw new \InvalidArgumentException(sprintf('Cannot map internal type "%s"', $internalType));
    }
}