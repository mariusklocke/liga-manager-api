<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Schema;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedPitchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\MatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\MatchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\PitchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\TeamLoader;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadDbAdapterInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Schema::class] = function () {
            return new Schema([
                'query'    => new QueryType(),
                'mutation' => new MutationType()
            ]);
        };
        $container[BufferedTeamLoader::class] = function () use ($container) {
            return new BufferedTeamLoader(new TeamLoader($container[ReadDbAdapterInterface::class]));
        };
        $container[BufferedMatchDayLoader::class] = function () use ($container) {
            return new BufferedMatchDayLoader(new MatchDayLoader($container[ReadDbAdapterInterface::class]));
        };
        $container[BufferedMatchLoader::class] = function () use ($container) {
            return new BufferedMatchLoader(new MatchLoader($container[ReadDbAdapterInterface::class]));
        };
        $container[BufferedPitchLoader::class] = function () use ($container) {
            return new BufferedPitchLoader(new PitchLoader($container[ReadDbAdapterInterface::class]));
        };
    }
}