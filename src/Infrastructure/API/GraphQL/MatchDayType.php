<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Filter\MatchFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;

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
                            /** @var MatchRepository $repo */
                            $repo = $context->getContainer()->get(MatchRepository::class);
                            return $repo->findMatches(
                                new MatchFilter(null, null, $root['id'], null)
                            );
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}