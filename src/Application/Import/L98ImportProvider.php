<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class L98ImportProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container[TeamMapper::class] = function () use ($container) {
            return new TeamMapper($container['orm.repository.team']);
        };
        $container[Importer::class] = function () use ($container) {
            return new Importer(
                $container[TeamMapper::class],
                new SeasonMapper($container['orm.repository.season']),
                new MatchMapper($container[TeamMapper::class])
            );
        };
    }
}