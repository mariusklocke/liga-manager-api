<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use DI;
use HexagonalPlayground\Application\Import\TeamMapperInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Symfony\Component\Console\Application;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Application::class => DI\factory(new ApplicationFactory()),
            TeamMapperInterface::class => DI\get(TeamMapper::class)
        ];
    }
}
