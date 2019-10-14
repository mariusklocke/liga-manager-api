<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RuntimeException;
use Swift_Mailer;
use Swift_NullTransport;
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
        $container[MailerInterface::class] = function () {
            $url = parse_url(Environment::get('EMAIL_URL'));

            $supportedProtocols = ['null', 'smtp', 'smtps'];
            if (!in_array($url['scheme'], $supportedProtocols)) {
                throw new RuntimeException(sprintf(
                    'Unsupported Email Protocol. Expected: "%s". Given: "%s"',
                    implode(',', $supportedProtocols),
                    $url['scheme']
                ));
            }

            if ($url['scheme'] === 'null') {
                $transport = new Swift_NullTransport();
            } else {
                $transport = new Swift_SmtpTransport($url['host'], $url['port'] ?? 25);
                if ($url['scheme'] === 'smtps') {
                    $transport->setEncryption('tls');
                }

                if (isset($url['user']) && !empty($url['user'])) {
                    $transport->setUsername($url['user']);
                }
                if (isset($url['pass']) && !empty($url['pass'])) {
                    $transport->setPassword($url['pass']);
                }
            }

            return new SwiftMailer(
                new Swift_Mailer($transport),
                Environment::get('EMAIL_SENDER_ADDRESS'),
                Environment::get('EMAIL_SENDER_NAME')
            );
        };
        $container[TemplateRendererInterface::class] = function () {
            return new TemplateRenderer(Environment::get('APP_HOME') . '/templates');
        };
    }
}