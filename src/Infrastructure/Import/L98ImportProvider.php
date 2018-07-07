<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class L98ImportProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container[L98ImportService::class] = function () use ($container) {
            return new L98ImportService(
                $container['orm.repository.match'],
                $container['orm.repository.team'],
                $container['orm.repository.season']
            );
        };
    }
}