<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar;

use DateTimeImmutable;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use HexagonalPlayground\Application\InputParser;

class DateType extends ScalarType
{
    public string $name = 'Date';

    public ?string $description = '';

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

    public function serialize($value)
    {
        $canCast = \is_scalar($value)
            || (\is_object($value) && \method_exists($value, '__toString'))
            || $value === null;

        if (! $canCast) {
            $notStringable = Utils::printSafe($value);
            throw new SerializationError("DateType cannot represent value: {$notStringable}");
        }

        return (string) $value;
    }
}
