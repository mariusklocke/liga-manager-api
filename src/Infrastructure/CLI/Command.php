<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\Timer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    public const NAME = null;

    /** @var AuthContext|null */
    private $authContext;

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct(static::NAME);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $timer = new Timer();
        $timer->start();

        $exitCode = parent::run($input, $output);

        if ($output->isVerbose()) {
            $memory = memory_get_peak_usage() / 1024 / 1024;
            $output->writeln(sprintf('Execution time: %d ms', $timer->stop()));
            $output->writeln(sprintf('Peak memory usage: %.1f MiB', $memory));
        }

        return $exitCode;
    }

    protected function getAuthContext(): AuthContext
    {
        if (null === $this->authContext) {
            $user = new User(
                'cli',
                'cli@example.com',
                '123456',
                'CLI',
                $this->getName(),
                User::ROLE_ADMIN
            );
            $this->authContext = new AuthContext($user);
        }

        return $this->authContext;
    }

    protected function getStyledIO(InputInterface $input, OutputInterface $output): StyleInterface
    {
        return new SymfonyStyle($input, $output);
    }
}
