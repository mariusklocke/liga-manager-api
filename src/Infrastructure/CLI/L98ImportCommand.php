<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\Import\L98FileParser;
use HexagonalPlayground\Infrastructure\Import\L98ImportService;
use HexagonalPlayground\Infrastructure\Import\L98TeamModel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class L98ImportCommand extends Command
{
    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

    /** @var L98ImportService */
    private $importService;

    /**
     * @param OrmTransactionWrapperInterface $transactionWrapper
     * @param L98ImportService $importService
     */
    public function __construct(OrmTransactionWrapperInterface $transactionWrapper, L98ImportService $importService)
    {
        parent::__construct();
        $this->transactionWrapper = $transactionWrapper;
        $this->importService = $importService;
    }

    protected function configure()
    {
        $this
            ->setName('app:import-season')
            ->setDefinition([
                new InputArgument('filepath', InputArgument::REQUIRED, 'Path to L98 season file')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDecorator = new SymfonyStyle($input, $output);
        $parser = new L98FileParser($input->getArgument('filepath'));
        foreach ($parser->getTeams() as $importableTeam) {
            $this->mapTeam($outputDecorator, $importableTeam);
        }
        $this->transactionWrapper->transactional(function () use ($parser) {
            $user = new User('import@example.com', '123456', 'bla', 'blubb');
            $this->importService->import($parser->getSeason(), $parser->getTeams(), $parser->getMatches(), $user);
        });
        $outputDecorator->success('Import completed successfully!');
    }

    /**
     * @param SymfonyStyle $outputDecorator
     * @param L98TeamModel $importableTeam
     */
    private function mapTeam(SymfonyStyle $outputDecorator, L98TeamModel $importableTeam)
    {
        $recommendedTeams = $this->importService->getTeamMappingRecommendations($importableTeam);
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
            $this->importService->addTeamMapping($importableTeam, $recommendedTeams[$answer]);
        } else {
            $selectedTeamIndex = array_search($answer, $choices);
            if ($selectedTeamIndex !== false && isset($recommendedTeams[$selectedTeamIndex])) {
                $this->importService->addTeamMapping($importableTeam, $recommendedTeams[$selectedTeamIndex]);
            }
        }
    }
}