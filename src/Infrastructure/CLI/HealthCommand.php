<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCommand extends Command
{
    public const NAME = 'app:health';

    protected function configure()
    {
        $this->addOption('retries', null, InputOption::VALUE_REQUIRED, 'Retry attempts');
        $this->addOption('skip', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyledIO($input, $output);

        /** @var HealthCheckInterface[] $checks */
        $checks = $this->container->get(HealthCheckInterface::class);
        $checks = array_filter($checks, function (HealthCheckInterface $healthCheck) use ($input) {
            return $healthCheck->getName() !== $input->getOption('skip');
        });

        $attempt = 1;
        $retries = (int)$input->getOption('retries');

        do {
            $errors = [];

            foreach ($checks as $check) {
                try {
                    $check();
                } catch (Exception $e) {
                    $errors[] = sprintf('Health check "%s" has failed: %s', $check->getName(), $e->getMessage());

                    if ($attempt <= $retries) {
                        $output->writeln(sprintf(
                            'Waiting for application to become healthy ... Attempt %d of %d',
                            $attempt,
                            $retries
                        ));
                        sleep(1);
                    }

                    break;
                }
            }

            $attempt++;
        } while (count($errors) > 0 && $attempt <= $retries);

        if (count($errors)) {
            $io->error($errors);
            return 1;
        }

        $io->success('Application is healthy');
        return 0;
    }
}
