<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query;

use JsonSerializable;

class Query implements JsonSerializable
{
    private string $name;
    private array $argTypes;
    private array $argValues;
    private array $fields;
    private const TEMPLATE = <<<'GRAPHQL'
query %s%s {
  %s%s {
    %s
  }
}
GRAPHQL;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->argTypes = [];
        $this->argValues = [];
        $this->fields = [];
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

    public function fields(array $fields): self
    {
        $this->fields = $fields;

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
            count($argNames) ? '(' . implode(', ', $argNames) . ')' : '',
            $this->stringifyFields($this->fields)
        );

        return [
            'query' => $query,
            'variables' => $this->argValues
        ];
    }

    private function stringifyFields(array $fields): string
    {
        $parts = [];

        foreach ($fields as $key => $value) {
            switch (gettype($value)) {
                case 'string':
                    $parts[] = $value;
                    break;
                case 'array':
                    $parts[] = $key . ' { ' . $this->stringifyFields($value) . ' } ';
                    break;
                default:
                    throw new \RuntimeException('Invalid field type');
            }
        }

        return implode(', ', $parts);
    }
}
