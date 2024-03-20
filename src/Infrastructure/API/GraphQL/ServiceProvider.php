<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use DI;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedLoaderInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedPitchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Schema::class => DI\factory(new SchemaFactory()),

            MutationTypeAggregator::class => DI\create()->constructor(
                DI\get(CommandInterface::class),
                DI\get(MutationMapper::class)
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

            BufferedLoaderInterface::class => [
                DI\get(BufferedMatchDayLoader::class),
                DI\get(BufferedMatchLoader::class),
                DI\get(BufferedPitchLoader::class),
                DI\get(BufferedTeamLoader::class)
            ]
        ];
    }
}
