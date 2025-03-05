<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Import\Executor;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class L98ImportCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:import:season');
        $this->setDescription('Import season data from L98 files');
        $this->addArgument('files', InputArgument::IS_ARRAY, 'Path to one or multiple L98 season files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $styledIo = $this->getStyledIO($input, $output);

        /** @var Executor $executor */
        $executor = $this->container->get(Executor::class);
        /** @var TeamMapper $teamMapper */
        $teamMapper = $this->container->get(TeamMapper::class);

        if ($input->isInteractive()) {
            $teamMapper->setStyledIo($styledIo);
        }

        foreach ($input->getArgument('files') as $path) {
            $styledIo->text('Started processing ' . $path);
            $inputFile = new File($path);
            $stream = $inputFile->open('r');
            $executor($stream, $this->getAuthContext(), $teamMapper);
            $stream->close();
            $styledIo->text('Finished processing ' . $path);
        }

        $styledIo->success('Import completed successfully!');
        return 0;
    }
}
