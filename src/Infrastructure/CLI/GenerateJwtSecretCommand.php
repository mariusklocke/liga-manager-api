<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJwtSecretCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:generate-jwt-secret');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $secret   = random_bytes(32);
        $styledIo = $this->getStyledIO($input, $output);

        $styledIo->section('Add the following line to your .env file');
        $styledIo->text('JWT_SECRET=' . bin2hex($secret));
        $styledIo->newLine();
        return 0;
    }
}