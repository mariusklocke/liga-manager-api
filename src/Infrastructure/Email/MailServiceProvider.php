<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use DI;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Swift_Mailer;
use Swift_Transport;

class MailServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            'app.home' => DI\env('APP_HOME'),
            MailerInterface::class => DI\get(SwiftMailer::class),
            Swift_Mailer::class => DI\autowire(),
            Swift_Transport::class => DI\factory(SwiftTransportFactory::class)
                ->parameter('url', DI\env('EMAIL_URL')),
            SwiftMailer::class => DI\create()
                ->constructor(
                    DI\get(Swift_Mailer::class),
                    DI\env('EMAIL_SENDER_ADDRESS'),
                    DI\env('EMAIL_SENDER_NAME')
                ),
            TemplateRendererInterface::class => DI\get(TemplateRenderer::class),
            TemplateRenderer::class => DI\create()
                ->constructor(DI\string('{app.home}/templates'))
        ];
    }
}