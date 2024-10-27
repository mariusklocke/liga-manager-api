<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use phpcent\Client;

class CentrifugoHealthCheck implements HealthCheckInterface
{
    private Client $centrifugo;

    public function __construct(Client $centrifugo)
    {
        $this->centrifugo = $centrifugo;
    }

    public function __invoke(): void
    {
        $this->centrifugo->info();
    }

    public function getName(): string
    {
        return 'centrifugo';
    }
}
