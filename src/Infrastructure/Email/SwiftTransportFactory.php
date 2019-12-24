<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use RuntimeException;
use Swift_NullTransport;
use Swift_SmtpTransport;
use Swift_Transport;

class SwiftTransportFactory
{
    public function __invoke(string $url): Swift_Transport
    {
        $url = parse_url($url);

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

        return $transport;
    }
}