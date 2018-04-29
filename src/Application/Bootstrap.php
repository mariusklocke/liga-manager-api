<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\EventPublisher;
use Psr\Container\ContainerInterface;

abstract class Bootstrap
{
    protected static function configureEventPublisher(ContainerInterface $container): void
    {
        EventPublisher::getInstance()->addSubscriber(
            new EventStoreSubscriber($container->get(EventStoreInterface::class))
        );
    }
}