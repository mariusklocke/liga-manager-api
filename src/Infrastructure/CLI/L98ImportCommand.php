<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Import\Importer;
use HexagonalPlayground\Application\Import\L98FileParser;
use HexagonalPlayground\Application\Import\L98TeamModel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class L98ImportCommand extends Command
{
    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

    /** @var Importer */
    private $importer;

    /**
     * @param OrmTransactionWrapperInterface $transactionWrapper
     * @param Importer $importer
     */
    public function __construct(OrmTransactionWrapperInterface $transactionWrapper, Importer $importer)
    {
        parent::__construct();
        $this->transactionWrapper = $transactionWrapper;
        $this->importer = $importer;
    }

    protected function configure()
    {
        $this
            ->setName('app:import-season')
            ->setDefinition([
                new InputArgument('file-pattern', InputArgument::REQUIRED, 'Pattern matching a set of L98 season files')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputDecorator = new SymfonyStyle($input, $output);
        foreach ($this->getImportFiles($input->getArgument('file-pattern')) as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            $outputDecorator->writeln('Start parsing ' . $fileInfo->getPathname());
            $this->importFile(new L98FileParser($fileInfo->getPathname()), $outputDecorator);
            $outputDecorator->writeln('Finished importing ' . $fileInfo->getPathname());
        }
        $outputDecorator->success('Import completed successfully!');
        return parent::execute($input, $output);
    }

    /**
     * @param string $pattern
     * @return \Iterator
     */
    private function getImportFiles(string $pattern): \Iterator
    {
        $fileIterator = new \GlobIterator($pattern);
        if (0 === $fileIterator->count()) {
            throw new \RuntimeException('Cannot find files matching pattern ' . $pattern);
        }

        return $fileIterator;
    }

    /**
     * @param L98FileParser $parser
     * @param SymfonyStyle $outputDecorator
     */
    private function importFile(L98FileParser $parser, SymfonyStyle $outputDecorator)
    {
        $season = $parser->parse();
        foreach ($season->getTeams() as $importableTeam) {
            $this->mapTeam($outputDecorator, $importableTeam);
        }
        $this->transactionWrapper->transactional(function () use ($season) {
            $this->importer->import($season, $this->getCliUser());
        });
    }

    /**
     * @param SymfonyStyle $outputDecorator
     * @param L98TeamModel $importableTeam
     */
    private function mapTeam(SymfonyStyle $outputDecorator, L98TeamModel $importableTeam)
    {
        $recommendedTeams = $this->importer->getTeamMapper()->getRecommendations($importableTeam);
        if (empty($recommendedTeams)) {
            return;
        }

        $choices = [];
        foreach ($recommendedTeams as $index => $recommendedTeam) {
            $choices[$index] = $recommendedTeam->getName();
        }
        $choices['*'] = '--- Import as a new team ---';
        $choiceQuestion = new ChoiceQuestion('Please choose how to map ' . $importableTeam->getName(), $choices);
        $answer = $outputDecorator->askQuestion($choiceQuestion);
        if (isset($recommendedTeams[$answer])) {
            $this->importer->getTeamMapper()->map($importableTeam, $recommendedTeams[$answer]);
        } else {
            $selectedTeamIndex = array_search($answer, $choices);
            if ($selectedTeamIndex !== false && isset($recommendedTeams[$selectedTeamIndex])) {
                $this->importer->getTeamMapper()->map($importableTeam, $recommendedTeams[$selectedTeamIndex]);
            }
        }
    }
}