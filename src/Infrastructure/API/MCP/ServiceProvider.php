<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\MCP\Tool\ListTeams;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Controller::class => DI\factory(function (ContainerInterface $container) {
                $tools = [
                    $container->get(ListTeams::class)
                ];

                return new Controller($container->get(ResponseFactoryInterface::class), $tools);
            })
        ];
    }
}