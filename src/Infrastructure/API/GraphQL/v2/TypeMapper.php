<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\MappingException;
use HexagonalPlayground\Infrastructure\API\GraphQL\TypeMapperInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\ContactInputType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\DatePeriodType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\GeoLocationInputType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\MatchAppointmentType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\MatchResultInputType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;

class TypeMapper extends \HexagonalPlayground\Infrastructure\API\GraphQL\TypeMapper implements TypeMapperInterface
{
    protected function mapInnerType(string $phpType): Type
    {
        switch ($phpType) {
            case 'string':
                return Type::string();
            case 'int':
            case 'integer':
                return Type::int();
            case 'float':
            case 'float|int':
                return Type::float();
            case 'DateTimeImmutable':
                return TypeRegistry::get(DateTimeType::class);
            case 'DatePeriod':
                return TypeRegistry::get(DatePeriodType::class);
            case 'ContactPerson':
                return TypeRegistry::get(ContactInputType::class);
            case 'GeographicLocation':
                return TypeRegistry::get(GeoLocationInputType::class);
            case 'MatchAppointment':
                return TypeRegistry::get(MatchAppointmentType::class);
            case 'MatchResult':
                return TypeRegistry::get(MatchResultInputType::class);
        }

        throw new MappingException(sprintf('Cannot map internal type "%s"', $phpType));
    }
}
