<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CheckHealthCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:health:check');
        $this->setDescription('Performs health checks');
        $this->addArgument('names', InputArgument::IS_ARRAY, 'Specify to use only selected checks ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var HealthCheckInterface[] $checks */
        $checks = $this->container->get(HealthCheckInterface::class);
        $names  = $input->getArgument('names') ?? [];

        if (count($names)) {
            $names  = array_flip($names);
            $checks = array_filter($checks, function (HealthCheckInterface $healthCheck) use ($names) {
                return array_key_exists($healthCheck->getName(), $names);
            });
        }

        $styledIo = $this->getStyledIO($input, $output);
        $exitCode = 0;

        foreach ($checks as $check) {
            $name = ucfirst($check->getName());
            try {
                $check();
            } catch (Throwable $exception) {
                $styledIo->error("$name is NOT healthy: " . $exception->getMessage());
                $exitCode = 1;
                continue;
            }
            $styledIo->success("$name is healthy");
        }

        return $exitCode;
    }
}
