<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Filter\EventFilter;
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

                    return $repo->findEventById($args['id']);
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

                    $filter = new EventFilter(
                        $args['start_date'] ?? null,
                        $args['end_date'] ?? null,
                        $args['type'] ?? null
                    );

                    return $repo->findLatestEvents($filter);
                }
            ]
        ];
    }
}