<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;

class PitchType extends ObjectType implements QueryTypeInterface
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
                    'label' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'location_longitude' => [
                        'type' => Type::nonNull(Type::float())
                    ],
                    'location_latitude' => [
                        'type' => Type::nonNull(Type::float())
                    ],
                    'contact' => [
                        'type' => ContactType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            $context->requireAuthContext($context->getRequest())->getUser();

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
            'pitch' => [
                'type' => static::getInstance(),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var PitchRepository $repo */
                    $repo = $context->getContainer()->get(PitchRepository::class);

                    return $repo->findPitchById($args['id']);
                }
            ],
            'allPitches' => [
                'type' => Type::listOf(static::getInstance()),
                'resolve' => function ($root, $args, AppContext $context) {
                    /** @var PitchRepository $repo */
                    $repo = $context->getContainer()->get(PitchRepository::class);

                    return $repo->findAllPitches();
                }
            ]
        ];
    }
}