<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\Bus\BatchCommandBus;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class L98ImportProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container[Executor::class] = function () use ($container) {
            return new Executor($container[BatchCommandBus::class]);
        };
    }
}