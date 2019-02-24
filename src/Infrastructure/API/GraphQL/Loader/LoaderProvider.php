<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\ReadDbAdapterInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LoaderProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
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