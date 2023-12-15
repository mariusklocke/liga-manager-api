<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use DI;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Config;
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
                /** @var Config $config */
                $config = $container->get(Config::class);

                $transport = Transport::fromDsn(
                    $config->emailUrl,
                    $container->get(EventDispatcherInterface::class)
                );

                return new SymfonyMailer(
                    new Mailer($transport),
                    $config->emailSenderAddress,
                    $config->emailSenderName
                );
            }),
            TemplateRendererInterface::class => DI\get(TemplateRenderer::class),
            TemplateRenderer::class => DI\factory(function (ContainerInterface $container) {
                return new TemplateRenderer(join(
                    DIRECTORY_SEPARATOR,
                    [$container->get('app.home'), 'templates']
                ));
            })
        ];
    }
}
