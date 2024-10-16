<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Email\HealthCheck;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CheckMailHealthCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:mail:health');
        $this->setDescription('Checks if mail server connection is healthy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var HealthCheck $check */
        $check = $this->container->get(HealthCheck::class);

        $styledIo = $this->getStyledIO($input, $output);
        try {
            $check();
        } catch (Throwable $exception) {
            $styledIo->error($exception->getMessage());
            return 1;
        }

        $styledIo->success('Mail server connection is healthy.');
        return 0;
    }
}
