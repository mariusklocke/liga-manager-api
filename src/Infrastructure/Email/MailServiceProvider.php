<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Infrastructure\Environment;
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
        $container[MailerInterface::class] = function () {
            try {
                $url = parse_url(Environment::get('EMAIL_URL'));
            } catch (\Exception $e) {
                $url = [
                    'scheme' => 'smtp',
                    'host' => Environment::get('SMTP_HOST'),
                    'port' => Environment::get('SMTP_PORT')
                ];
            }

            if ($url['scheme'] !== 'smtp') {
                throw new \RuntimeException(
                    sprintf('Unsupported Email Protocol. Expected: "smtp". Given: "%s" ', $url['scheme'])
                );
            }

            $transport = new Swift_SmtpTransport($url['host'], $url['port'] ?? 25);

            if (isset($url['user']) && !empty($url['user'])) {
                $transport->setUsername($url['user']);
            }
            if (isset($url['pass']) && !empty($url['pass'])) {
                $transport->setPassword($url['pass']);
            }

            try {
                $senderAddress = Environment::get('EMAIL_SENDER_ADDRESS');
                $senderName = Environment::get('EMAIL_SENDER_NAME');
            } catch (\Exception $e) {
                list($senderAddress, $senderName) = explode(';', Environment::get('EMAIL_SENDER'));
            }

            return new SwiftMailer(
                new Swift_Mailer($transport),
                $senderAddress,
                $senderName
            );
        };
        $container[TemplateRenderer::class] = function () {
            return new TemplateRenderer(Environment::get('APP_HOME') . '/templates');
        };
    }
}