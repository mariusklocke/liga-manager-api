<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CommandProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container['cli.command'] = [
            'app:load-fixtures' => function () use ($container) {
                return new LoadFixturesCommand($container['commandBus']);
            },
            'app:create-user' => function () use ($container) {
                return new CreateUserCommand($container['commandBus']);
            },
            'app:import-matches' => function () use ($container) {
                return new ImportMatchesCommand($container['batchCommandBus']);
            },
            'app:reset-password' => function () use ($container) {
                return new ResetPasswordCommand(
                    $container[MailerInterface::class],
                    $container[TemplateRenderer::class]
                );
            },
            'app:list-events' => function () use ($container) {
                return new ListEventsCommand($container[EventStoreInterface::class]);
            }
        ];
    }
}