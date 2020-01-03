<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use GlobIterator;
use HexagonalPlayground\Application\Import\Executor;
use HexagonalPlayground\Infrastructure\Filesystem\FileStream;
use Iterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

class L98ImportCommand extends Command
{
    public const NAME = 'app:import-season';

    /** @var Executor */
    private $executor;

    /** @var TeamMapper */
    private $teamMapper;

    /**
     * @param Executor $executor
     * @param TeamMapper $teamMapper
     */
    public function __construct(Executor $executor, TeamMapper $teamMapper)
    {
        parent::__construct();
        $this->executor = $executor;
        $this->teamMapper = $teamMapper;
    }

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('path', InputArgument::REQUIRED, 'Path to L98 season files (wildcards allowed)')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $styledIo = $this->getStyledIO($input, $output);

        if ($input->isInteractive()) {
            $this->teamMapper->setStyledIo($styledIo);
        }

        /** @var SplFileInfo $fileInfo */
        foreach ($this->getFileIterator($input->getArgument('path')) as $fileInfo) {
            $this->importFile($fileInfo->getPathname(), $styledIo);
        }

        $styledIo->success('Import completed successfully!');
        return 0;
    }

    /**
     * @param string $pattern
     * @return Iterator
     */
    private function getFileIterator(string $pattern): Iterator
    {
        $fileIterator = new GlobIterator($pattern);
        if (0 === $fileIterator->count()) {
            throw new RuntimeException('Cannot find files matching pattern ' . $pattern);
        }

        return $fileIterator;
    }

    /**
     * @param string $path
     * @param StyleInterface $styledIo
     */
    private function importFile(string $path, StyleInterface $styledIo): void
    {
        $styledIo->text('Started processing ' . $path);

        $this->executor->__invoke(
            new FileStream($path),
            $this->getCliUser(),
            $this->teamMapper
        );

        $styledIo->text('Finished processing ' . $path);
    }
}
