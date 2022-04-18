<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;

class CompetitionType extends UnionType
{
    public function __construct()
    {
        parent::__construct([
            'types' => [
                TypeRegistry::get(SeasonType::class),
                TypeRegistry::get(TournamentType::class)
            ],
            'resolveType' => function ($value): ObjectType {
                if (isset($value['state'])) {
                    return TypeRegistry::get(SeasonType::class);
                } else {
                    return TypeRegistry::get(TournamentType::class);
                }
            },
        ]);
    }
}
