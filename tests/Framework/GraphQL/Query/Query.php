<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL\Query;

use HexagonalPlayground\Tests\Framework\GraphQL\NamedOperation;
use LogicException;

class Query implements NamedOperation
{
    private string $name;
    private array $fields;
    private array $argTypes;
    private array $argValues;

    /**
     * @param string $name
     * @param array $fields
     * @param array $argTypes
     * @param array $argValues
     */
    public function __construct(string $name, array $fields, array $argTypes = [], array $argValues = [])
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
        $this->fields = $fields;
        $this->argTypes = $argTypes;
        $this->argValues = $argValues;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array $argTypes
     * @return Query
     */
    public function withArgTypes(array $argTypes): self
    {
        $clone = clone $this;
        $clone->argTypes = array_merge($clone->argTypes, $argTypes);

        return $clone;
    }

    /**
     * @param array $argValues
     * @return Query
     */
    public function withArgValues(array $argValues): self
    {
        $clone = clone $this;
        $clone->argValues = array_merge($clone->argValues, $argValues);

        return $clone;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $argTypes = [];
        $argNames = [];

        foreach ($this->argTypes as $name => $type) {
            $argTypes[] = sprintf('$%s: %s', $name, $type);
            $argNames[] = sprintf('%s: $%s', $name, $name);
        }

        $query = sprintf(
            'query %s%s { %s%s { %s } }',
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

    /**
     * @param array $fields
     * @return string
     */
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
