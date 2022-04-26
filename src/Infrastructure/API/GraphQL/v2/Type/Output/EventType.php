<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\PaginationType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter\EventFilterType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\RangeFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\EventRepository;
use Iterator;

class EventType extends ObjectType implements QueryTypeInterface
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'occurredAt' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateTimeType::class))
                    ],
                    'type' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ]);
    }

    public function getQueries(): array
    {
        return [
            'event' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var EventRepository $repo */
                    $repo = $context->getContainer()->get(EventRepository::class);

                    return $repo->findById($args['id']);
                }
            ],
            'eventList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(EventFilterType::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var EventRepository $repo */
                    $repo = $context->getContainer()->get(EventRepository::class);
                    $converter = new FieldNameConverter();

                    $filters = [];
                    if (isset($args['filter'])) {
                        $filters = $this->buildFilters($args['filter']);
                    }

                    $sortings = [new Sorting('occurred_at', Sorting::DIRECTION_DESCENDING)];

                    $pagination = null;
                    if (isset($args['pagination'])) {
                        $pagination = new Pagination($args['pagination']['limit'], $args['pagination']['offset']);
                    }

                    return $converter->convert($repo->findMany($filters, $sortings, $pagination));
                }
            ]
        ];
    }

    /**
     * @param array $values
     * @return Iterator|Filter[]
     */
    private function buildFilters(array $values): Iterator
    {
        if (isset($values['occurredAfter']) || isset($values['occurredBefore'])) {
            yield new RangeFilter(
                'occurred_at',
                Filter::MODE_INCLUDE,
                $values['occurredAfter'] ?? null,
                $values['occurredBefore'] ?? null
            );
        }

        if (isset($values['types'])) {
            yield new EqualityFilter('type', Filter::MODE_INCLUDE, $values['types']);
        }
    }
}
