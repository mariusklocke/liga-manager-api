<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use DI;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            MailerInterface::class => DI\get(SymfonyMailer::class),
            SymfonyMailer::class => DI\autowire(),
            Mailer::class => DI\factory(function (ContainerInterface $container) {
                return new Mailer($container->get(TransportInterface::class));
            }),
            TransportInterface::class => DI\factory(new TransportFactory()),
            HealthCheck::class => DI\autowire(),
            HealthCheckInterface::class => DI\add(DI\get(HealthCheck::class))
        ];
    }
}
