<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use DI;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            MailerInterface::class => DI\get(SymfonyMailer::class),
            SymfonyMailer::class => DI\factory(function (ContainerInterface $container) {
                return new SymfonyMailer(
                    $container->get(Mailer::class),
                    $container->get('config.email.emailSenderAddress'),
                    $container->get('config.email.emailSenderName')
                );
            }),
            TemplateRendererInterface::class => DI\get(TemplateRenderer::class),
            TemplateRenderer::class => DI\factory(function (ContainerInterface $container) {
                /** @var FilesystemService $filesystem */
                $filesystem = $container->get(FilesystemService::class);

                return new TemplateRenderer(
                    $filesystem->joinPaths([$container->get('app.home'), 'templates'])
                );
            }),
            Mailer::class => DI\factory(function (ContainerInterface $container) {
                return new Mailer($container->get(TransportInterface::class));
            }),
            TransportInterface::class => DI\factory(function (ContainerInterface $container) {
                return Transport::fromDsn(
                    $container->get('config.email.emailUrl'),
                    $container->get(EventDispatcherInterface::class)
                );
            }),
            HealthCheckInterface::class => DI\add(DI\get(HealthCheck::class))
        ];
    }
}
