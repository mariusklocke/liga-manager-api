<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;
use HexagonalPlayground\Application\InputParser;

class DateType extends ScalarType
{
    use SingletonTrait;

    public string $name = 'Date';

    /**
     * @param mixed $value
     * @return DateTimeImmutable|null
     */
    public function parseValue($value): ?DateTimeImmutable
    {
        return $value !== null ? InputParser::parseDate($value) : null;
    }

    /**
     * @param Node $valueNode
     * @param array|null $variables
     * @return DateTimeImmutable|null
     */
    public function parseLiteral($valueNode, ?array $variables = null): ?DateTimeImmutable
    {
        if ($valueNode instanceof StringValueNode) {
            return $this->parseValue($valueNode->value);
        }

        $notString = Printer::doPrint($valueNode);
        throw new Error("DateType cannot represent a non string value: {$notString}", $valueNode);
    }

    /**
     * @param mixed $value
     * @return string
     * @throws SerializationError
     */
    public function serialize($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $type = gettype($value);
        if ($type === 'object') {
            $type = get_class($value);
        }
        throw new SerializationError("DateType cannot represent internal type: {$type}");
    }
}
