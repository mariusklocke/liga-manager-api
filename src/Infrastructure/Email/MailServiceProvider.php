<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use DI;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Environment;
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
            'app.home' => DI\env('APP_HOME'),
            MailerInterface::class => DI\get(SymfonyMailer::class),
            SymfonyMailer::class => DI\factory(function (ContainerInterface $container) {
                $transport = Transport::fromDsn(
                    getenv('EMAIL_URL') ?: 'null://localhost',
                    $container->get(EventDispatcherInterface::class)
                );

                return new SymfonyMailer(
                    new Mailer($transport),
                    getenv('EMAIL_SENDER_ADDRESS') ?: 'noreply@example.com',
                    getenv('EMAIL_SENDER_NAME') ?: 'No Reply'
                );
            }),
            TemplateRendererInterface::class => DI\get(TemplateRenderer::class),
            TemplateRenderer::class => DI\create()
                ->constructor(DI\string('{app.home}/templates'))
        ];
    }
}
