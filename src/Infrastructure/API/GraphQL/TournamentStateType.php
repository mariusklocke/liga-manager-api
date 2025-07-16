<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\EnumType;
use HexagonalPlayground\Domain\Tournament;

class TournamentStateType extends EnumType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'values' => [
                Tournament::STATE_PREPARATION => [
                    'value' => Tournament::STATE_PREPARATION
                ],
                Tournament::STATE_PROGRESS => [
                    'value' => Tournament::STATE_PROGRESS
                ],
                Tournament::STATE_ENDED => [
                    'value' => Tournament::STATE_ENDED
                ]
            ]
        ];
        parent::__construct($config);
    }
}