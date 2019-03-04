<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\EnumType;
use HexagonalPlayground\Domain\Season;

class SeasonStateType extends EnumType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'values' => [
                Season::STATE_PREPARATION => [
                    'value' => Season::STATE_PREPARATION
                ],
                Season::STATE_PROGRESS => [
                    'value' => Season::STATE_PROGRESS
                ],
                Season::STATE_ENDED => [
                    'value' => Season::STATE_ENDED
                ]
            ]
        ];
        parent::__construct($config);
    }
}