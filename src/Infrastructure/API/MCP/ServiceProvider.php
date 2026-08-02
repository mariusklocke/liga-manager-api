<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP;

use DI;
use HexagonalPlayground\Infrastructure\API\MCP\Tool\ListTeams;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ServiceProvider
{
    public function register(): array
    {
        return [
            Controller::class => DI\factory(function (ContainerInterface $container) {
                return new Controller(
                    $container->get(ResponseFactoryInterface::class),
                    $container->get(Tool::class)
                );
            }),
            Tool::class => [
                ListTeams::class,
            ]
        ];
    }
}