<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use DI;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class MailServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            MailerInterface::class => DI\get(SymfonyMailer::class),
            SymfonyMailer::class => DI\factory(function (ContainerInterface $container) {
                $transport = Transport::fromDsn(
                    $container->get('config.email.emailUrl'),
                    $container->get(EventDispatcherInterface::class)
                );

                return new SymfonyMailer(
                    new Mailer($transport),
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
            'config.email.emailUrl' => DI\env('EMAIL_URL', 'null://localhost'),
            'config.email.emailSenderAddress' => DI\env('EMAIL_SENDER_ADDRESS', 'noreply@example.com'),
            'config.email.emailSenderName' => DI\env('EMAIL_SENDER_NAME', 'No Reply')
        ];
    }
}
