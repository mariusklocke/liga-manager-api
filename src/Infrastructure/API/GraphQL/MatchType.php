<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;

class MatchType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::string()
                    ],
                    'home_team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            return $this->resolveTeam($root['home_team_id'], $context);
                        }
                    ],
                    'guest_team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            return $this->resolveTeam($root['guest_team_id'], $context);
                        }
                    ],
                    'kickoff' => [
                        'type' => Type::string()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    private function resolveTeam(string $teamId, AppContext $context): Deferred
    {
        /** @var BufferedTeamLoader $loader */
        $loader = $context->getContainer()->get(BufferedTeamLoader::class);
        $loader->addTeam($teamId);
        return new Deferred(function() use ($loader, $teamId) {
            return $loader->getByTeam($teamId);
        });
    }
}