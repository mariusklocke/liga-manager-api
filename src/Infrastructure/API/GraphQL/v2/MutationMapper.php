<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\v2\CommandInterface;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\CustomObjectType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\MutationResponse;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\Timer;
use TypeError;

class MutationMapper
{
    /** @var TypeMapper */
    private TypeMapper $typeMapper;

    /** @var AuthReader */
    private AuthReader $authReader;

    /**
     * @param TypeMapper $typeMapper
     * @param AuthReader $authReader
     */
    public function __construct(TypeMapper $typeMapper, AuthReader $authReader)
    {
        $this->typeMapper = $typeMapper;
        $this->authReader = $authReader;
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
                'type' => TypeRegistry::get(MutationResponse::class),
                'resolve' => function ($val, $argValues, AppContext $context) use ($commandClass, $argTypes) {
                    try {
                        $command = $this->createCommand($commandClass, $argTypes, $argValues);
                    } catch (TypeError $typeError)  {
                        throw new InvalidInputException($typeError->getMessage());
                    }

                    if (method_exists($command, 'withBaseUri')) {
                        $command = $command->withBaseUri($context->getRequest()->getUri());
                    }

                    $authContext = null;
                    if ($this->authReader->hasAuthContext($context->getRequest())) {
                        $authContext = $this->authReader->requireAuthContext($context->getRequest());
                    }

                    $timer = new Timer();
                    $timer->start();

                    /** @var CommandBus $commandBus */
                    $commandBus = $context->getContainer()->get(CommandBus::class);
                    $commandBus->execute($command, $authContext);

                    return ['executionTime' => $timer->stop()];
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
            $finalArgs[] = $this->parseValue($argValues[$name] ?? null, $type);
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
        $constructor = (new \ReflectionClass($commandClass))->getConstructor();
        if (null === $constructor) {
            return [];
        }

        $constructorDocBlock = $constructor->getDocComment();
        if (!is_string($constructorDocBlock)) {
            return [];
        }

        $args = [];
        foreach (explode("\n", $constructorDocBlock) as $line) {
            $matches = [];
            if (preg_match('/@param\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $name = substr($matches[2], 1); // strip $ character
                $args[$name] = $matches[1];
            }
        }

        return $args;
    }
}
