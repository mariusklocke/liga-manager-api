<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;

class TeamType extends ObjectType implements QueryTypeInterface
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'created_at' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'contact' => [
                        'type' => ContactType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            $context->requireAuthContext($context->getRequest());

                            return $root['contact'] ?? null;
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    public function getQueries(): array
    {
        return [
            'allTeams' => [
                'type' => Type::listOf(static::getInstance()),
                'resolve' => function ($root, $args, AppContext $context) {
                    /** @var TeamRepository $repo */
                    $repo = $context->getContainer()->get(TeamRepository::class);

                    return $repo->findAllTeams();
                }
            ],
            'team' => [
                'type' => static::getInstance(),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var TeamRepository $repo */
                    $repo = $context->getContainer()->get(TeamRepository::class);

                    return $repo->findTeamById($args['id']);
                }
            ]
        ];
    }
}
