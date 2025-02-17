<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Infrastructure\Timer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    protected ContainerInterface $container;
    protected AuthContext $authContext;
    protected StyleInterface $style;

    public function __construct(ContainerInterface $container, AuthContext $authContext)
    {
        parent::__construct();
        $this->container = $container;
        $this->authContext = $authContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->style = new SymfonyStyle($input, $output);

        /** @var LoggerInterface $logger */
        $logger = $this->container->get(LoggerInterface::class);
        $logger->debug("Starting command {$this->getName()}");

        $timer = new Timer();
        $timer->start();

        $exitCode = parent::run($input, $output);
        $executionTime = $timer->stop();
        $memoryUsage = memory_get_peak_usage() / 1024 / 1024;

        $logger->debug("Finished command {$this->getName()} with exit code $exitCode");
        $logger->debug("Execution time: $executionTime ms");
        $logger->debug(sprintf('Peak memory usage: %.1f MiB', $memoryUsage));

        return $exitCode;
    }

    protected function getAuthContext(): AuthContext
    {
        return $this->authContext;
    }

    protected function getStyledIO(InputInterface $input, OutputInterface $output): StyleInterface
    {
        return new SymfonyStyle($input, $output);
    }
}
