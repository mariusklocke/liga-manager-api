<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCommand extends Command
{
    public const NAME = 'app:health';

    /** @var HealthCheckInterface[] */
    private $checks;

    /**
     * @param HealthCheckInterface[] $checks
     */
    public function __construct(array $checks)
    {
        parent::__construct();
        $this->checks = $checks;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;
        $io = $this->getStyledIO($input, $output);

        foreach ($this->checks as $check) {
            try {
                $check();
                $io->success($check->getDescription());
            } catch (Exception $e) {
                $exitCode = 1;
                $io->error($e);
            }
        }

        return $exitCode;
    }
}
