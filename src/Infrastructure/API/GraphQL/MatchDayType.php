<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;

class MatchDayType extends ObjectType
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
                    'number' => [
                        'type' => Type::int()
                    ],
                    'matches' => [
                        'type' => Type::listOf(MatchType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedMatchLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchLoader::class);
                            $loader->addMatchDay($root['id']);
                            return new Deferred(function() use ($root, $loader) {
                                return $loader->getByMatchDay($root['id']);
                            });
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}