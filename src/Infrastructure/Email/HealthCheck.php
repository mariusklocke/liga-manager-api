<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class HealthCheck implements HealthCheckInterface
{
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function __invoke(): void
    {
        if ($this->transport instanceof SmtpTransport) {
            $this->transport->executeCommand(sprintf("HELO %s\r\n", $this->transport->getLocalDomain()), [250]);
        }
    }

    public function getName(): string
    {
        return 'email';
    }
}
