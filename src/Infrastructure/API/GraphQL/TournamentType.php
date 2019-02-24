<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;

class TournamentType extends ObjectType
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
                    'name' => [
                        'type' => Type::string()
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
}