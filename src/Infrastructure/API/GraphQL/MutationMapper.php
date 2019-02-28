<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Exception\InvalidInputException;
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

        $argTypes = $this->buildArgumentTypes($commandClass);

        return [
            $name => [
                'args' => $argTypes,
                'type' => Type::boolean(),
                'resolve' => function ($val, $argValues, AppContext $context) use ($commandClass, $argTypes) {
                    $command = $this->createCommand($commandClass, $argTypes, $argValues);
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

    private function createCommand(string $commandClass, array $argTypes, array $argValues): CommandInterface
    {
        $finalArgs = [];
        foreach ($argTypes as $name => $type) {
            if (!isset($argValues[$name])) {
                throw new InvalidInputException(sprintf('Missing value for argument "%s"', $name));
            }

            $finalArgs[] = $this->parseValue($argValues[$name], $type);
        }
        return new $commandClass(...$finalArgs);
    }

    private function parseValue($inputVal, Type $type)
    {
        if ($inputVal === null) {
            return null;
        }

        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        if ($type instanceof CustomObjectType) {
            return $type->parseCustomValue($inputVal);
        }

        if ($type instanceof ListOfType && $type->getWrappedType() instanceof CustomObjectType && is_array($inputVal)) {
            /** @var CustomObjectType $wrappedType */
            $wrappedType = $type->getWrappedType();
            return array_map(function ($innerVal) use ($wrappedType) {
                return $wrappedType->parseCustomValue($innerVal);
            }, $inputVal);
        }

        return $inputVal;
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