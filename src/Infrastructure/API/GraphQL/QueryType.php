<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;

class QueryType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'season' => [
                        'type' => SeasonType::getInstance(),
                        'description' => 'Get a single season',
                        'args' => [
                            'id' => Type::string()
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var SeasonRepository $repo */
                            $repo = $context->getContainer()->get(SeasonRepository::class);

                            return $repo->findSeasonById($args['id']);
                        }
                    ],
                    'allSeasons' => [
                        'type' => Type::listOf(SeasonType::getInstance()),
                        'description' => 'Get a list of all seasons',
                        'resolve' => function ($root, $args, AppContext $context) {
                            /** @var SeasonRepository $repo */
                            $repo = $context->getContainer()->get(SeasonRepository::class);

                            return $repo->findAllSeasons();
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
