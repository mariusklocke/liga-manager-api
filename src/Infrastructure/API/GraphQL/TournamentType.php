<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;

class TournamentType extends ObjectType implements QueryTypeInterface
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
                    'state' => [
                        'type' => Type::nonNull(TournamentStateType::getInstance())
                    ],
                    'rounds' => [
                        'type' => Type::listOf(MatchDayType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedMatchDayLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchDayLoader::class);
                            $loader->addTournament($root['id']);
                            return new Deferred(function() use ($root, $loader) {
                                return $loader->getByTournament($root['id']);
                            });
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
            'tournament' => [
                'type' => static::getInstance(),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var TournamentRepository $repo */
                    $repo = $context->getContainer()->get(TournamentRepository::class);

                    return $repo->findById($args['id']);
                }
            ],
            'allTournaments' => [
                'type' => Type::listOf(static::getInstance()),
                'resolve' => function ($root, $args, AppContext $context) {
                    /** @var TournamentRepository $repo */
                    $repo = $context->getContainer()->get(TournamentRepository::class);

                    return $repo->findMany();
                }
            ]
        ];
    }
}
