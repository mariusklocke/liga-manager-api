<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use DI;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Schema::class => DI\factory(new SchemaFactory()),

            MutationTypeAggregator::class => DI\create()->constructor(
                DI\get(CommandInterface::class),
                DI\factory(function () {
                    return new MutationMapper(
                        new TypeMapper(),
                        new AuthReader(),
                        true
                    );
                })
            ),

            QueryTypeAggregator::class => DI\create()->constructor(
                DI\get(QueryTypeInterface::class)
            ),

            QueryTypeInterface::class => DI\factory(function () {
                return [
                    EventType::getInstance(),
                    MatchType::getInstance(),
                    PitchType::getInstance(),
                    SeasonType::getInstance(),
                    TeamType::getInstance(),
                    TournamentType::getInstance(),
                    UserType::getInstance()
                ];
            }),

            __NAMESPACE__ . '\Loader\*Loader' => DI\autowire(),
        ];
    }
}
