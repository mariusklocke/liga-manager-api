<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\RangeFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\EventRepository;

class EventType extends ObjectType implements QueryTypeInterface
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
                    'occurred_at' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'type' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    public function getQueries(): array
    {
        return [
            'event' => [
                'type' => static::getInstance(),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var EventRepository $repo */
                    $repo = $context->getContainer()->get(EventRepository::class);

                    return $repo->findById($args['id']);
                }
            ],
            'latestEvents' => [
                'type' => Type::listOf(static::getInstance()),
                'args' => [
                    'start_date' => DateType::getInstance(),
                    'end_date' => DateType::getInstance(),
                    'type' => Type::string(),
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var EventRepository $repo */
                    $repo = $context->getContainer()->get(EventRepository::class);

                    $filters = [];

                    if (isset($args['start_date']) || isset($args['end_date'])) {
                        $filters[] = new RangeFilter(
                            $repo->getField('occurred_at'),
                            Filter::MODE_INCLUDE,
                            $args['start_date'] ?? null,
                            $args['end_date'] ?? null
                        );
                    }

                    if (isset($args['type'])) {
                        $filters[] = new EqualityFilter(
                            $repo->getField('type'),
                            Filter::MODE_INCLUDE,
                            [$args['type']]
                        );
                    }

                    return $repo->findMany(
                        $filters,
                        [new Sorting($repo->getField('occurred_at'), Sorting::DIRECTION_DESCENDING)],
                        new Pagination(50, 0)
                    );
                }
            ]
        ];
    }
}
