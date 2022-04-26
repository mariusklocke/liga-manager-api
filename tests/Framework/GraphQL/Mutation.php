<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use JsonSerializable;

class Mutation implements JsonSerializable
{
    private string $name;
    private array $argTypes;
    private array $argValues;
    private const TEMPLATE = <<<'GRAPHQL'
mutation %s%s {
  %s%s
}
GRAPHQL;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->argTypes = [];
        $this->argValues = [];
    }

    public function argTypes(array $argTypes): self
    {
        $this->argTypes = $argTypes;

        return $this;
    }

    public function argValues(array $argValues): self
    {
        $this->argValues = $argValues;

        return $this;
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
            self::TEMPLATE,
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
