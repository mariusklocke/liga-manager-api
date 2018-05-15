<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Swift_Mailer;
use Swift_SmtpTransport;

class MailServiceProvider implements ServiceProviderInterface
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
        $container[MailerInterface::class] = function () use ($container) {
            $transport = new Swift_SmtpTransport(getenv('SMTP_HOST'), getenv('SMTP_PORT'));
            list($senderAddress, $senderName) = explode(';', getenv('EMAIL_SENDER'));
            return new SwiftMailer(
                new Swift_Mailer($transport),
                $senderAddress,
                $senderName
            );
        };
        $container[TemplateRenderer::class] = function () use ($container) {
            return new TemplateRenderer(__DIR__ . '/../templates');
        };
    }
}