<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use RuntimeException;

class HealthCheck implements HealthCheckInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __invoke(): void
    {
        $parsedUrl = parse_url($this->config->getValue('email.url', 'null://localhost'));

        $scheme   = $parsedUrl['scheme'] ?? null;
        $hostname = $parsedUrl['host'] ?? null;
        $port     = $parsedUrl['port'] ?? 465;
        $timeout  = 15;

        if ($scheme === 'smtp' || $scheme === 'smtps') {
            $socket = fsockopen($hostname, $port, $errorCode, $errorMessage, $timeout);
            if (!is_resource($socket)) {
                throw new RuntimeException("Failed to connect to SMTP server: $errorMessage (Code: $errorCode)");
            }
            fclose($socket);
        }
    }

    public function getName(): string
    {
        return 'email';
    }
}
