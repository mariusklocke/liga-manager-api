<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceModeCommand extends Command
{
    public const NAME = 'app:maintenance';

    protected function configure(): void
    {
        $this->setDescription('Configure maintenance mode');
        $this->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Set Maintenance Mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io        = $this->getStyledIO($input, $output);
        $mode      = (string)$input->getOption('mode');
        $filePath  = Environment::get('APP_HOME') . '/.maintenance_mode';
        $isEnabled = file_exists($filePath);

        switch ($mode) {
            case 'on':
                if (file_put_contents($filePath, '') !== false) {
                    $io->text('Maintenance mode has been enabled');
                }
                break;
            case 'off':
                if (unlink($filePath)) {
                    $io->text('Maintenance mode has been disabled');
                }
                break;
            default:
                $io->text(sprintf('Maintenance mode is %s', $isEnabled ? 'on' : 'off'));
                break;
        }

        return 0;
    }
}
