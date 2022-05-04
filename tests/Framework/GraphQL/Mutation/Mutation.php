<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Mutation;

use JsonSerializable;
use LogicException;

class Mutation implements JsonSerializable
{
    private string $name;
    private array $argTypes;
    private array $argValues;

    public function __construct(string $name, array $argTypes = [], array $argValues = [])
    {
        foreach ($argValues as $field => $value) {
            if (!isset($argTypes[$field])) {
                throw new LogicException('Missing argument type definition for field ' . $field);
            }
        }

        foreach ($argTypes as $field => $type) {
            if (str_ends_with($type, '!') && !isset($argValues[$field])) {
                throw new LogicException('Missing value for required field ' . $field);
            }
        }

        $this->name = $name;
        $this->argTypes = $argTypes;
        $this->argValues = $argValues;
    }

    public function jsonSerialize(): array
    {
        $argTypes = [];
        $argNames = [];

        foreach ($this->argTypes as $name => $type) {
            $argTypes[] = sprintf('$%s: %s', $name, $type);
            $argNames[] = sprintf('%s: $%s', $name, $name);
        }

        $query = sprintf(
            'mutation %s%s { %s%s { executionTime } }',
            $this->name,
            count($argTypes) ? '(' . implode(', ', $argTypes) . ')' : '',
            $this->name,
            count($argNames) ? '(' . implode(', ', $argNames) . ')' : ''
        );

        return [
            'query' => $query,
            'variables' => $this->argValues
        ];
    }
}
