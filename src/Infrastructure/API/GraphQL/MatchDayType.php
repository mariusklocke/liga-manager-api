<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Filter\MatchFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use Psr\Container\ContainerInterface;

class MatchDayType extends ObjectType
{
    use SingletonTrait;

    const NAME = 'MatchDay';

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
                        'resolve' => function ($root, $args, ContainerInterface $container) {
                            /** @var MatchRepository $repo */
                            $repo = $container->get(MatchRepository::class);
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