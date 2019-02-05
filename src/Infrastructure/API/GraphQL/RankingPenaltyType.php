<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;

class RankingPenaltyType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var TeamRepository $repo */
                            $repo = $context->getContainer()->get(TeamRepository::class);

                            return $repo->findTeamById($root['team_id']);
                        }
                    ],
                    'reason' => [
                        'type' => Type::string(),
                    ],
                    'created_at' => [
                        'type' => Type::string()
                    ],
                    'points' => [
                        'type' => Type::int()
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}