<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Infrastructure\Config;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\NullTransportFactory;
use Symfony\Component\Mailer\Transport\SendmailTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;

class TransportFactory
{
    public function __invoke(ContainerInterface $container): TransportInterface
    {
        /** @var Config $config */
        $config = $container->get(Config::class);

        $url    = parse_url($config->getValue('email.url', 'null://localhost'));
        $scheme = $url['scheme'] ?? null;
        $host   = $url['host'] ?? null;
        $port   = $url['port'] ?? null;
        $user   = ($url['user'] ?? '') ? rawurldecode($url['user']) : null;
        $pass   = ($url['pass'] ?? '') ? rawurldecode($url['pass']) : null;
        parse_str($url['query'] ?? '', $query);

        $passwordFile = $config->getValue('email.password.file');
        if ($passwordFile) {
            $pass = file_get_contents($passwordFile) ?: throw new InvalidArgumentException("Failed to read email password from $passwordFile");
        }

        $dsn = new Dsn($scheme, $host, $user, $pass, $port, $query);
        $factories = [
            new NullTransportFactory($container->get(EventDispatcherInterface::class)),
            new SendmailTransportFactory($container->get(EventDispatcherInterface::class)),
            new EsmtpTransportFactory($container->get(EventDispatcherInterface::class)),
            new NativeTransportFactory($container->get(EventDispatcherInterface::class))
        ];  

        return (new Transport($factories))->fromDsnObject($dsn);
    }
}