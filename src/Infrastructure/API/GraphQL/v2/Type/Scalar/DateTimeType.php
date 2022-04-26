<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar;

use DateTimeImmutable;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\StringType;
use HexagonalPlayground\Application\InputParser;

class DateTimeType extends StringType
{
    public $name = 'DateTime';

    public $description = '';

    /**
     * @param mixed $value
     * @return DateTimeImmutable|null
     */
    public function parseValue($value): ?DateTimeImmutable
    {
        return $value !== null ? InputParser::parseDateTime(parent::parseValue($value)) : null;
    }

    /**
     * @param Node $valueNode
     * @param array|null $variables
     * @return DateTimeImmutable|null
     */
    public function parseLiteral($valueNode, ?array $variables = null): ?DateTimeImmutable
    {
        return $this->parseValue(parent::parseLiteral($valueNode, $variables));
    }
}
