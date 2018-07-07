<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\Import\L98ImportService;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
        $this->importService->init($input->getArgument('filepath'));
        $helper = new QuestionHelper();
        foreach ($this->importService->getImportableTeams() as $importableTeam) {
            $recommendedTeams = $this->importService->getTeamMappingRecommendations($importableTeam);
            $output->writeln('Please choose how to map ' . $importableTeam->getName());
            foreach ($recommendedTeams as $index => $recommendedTeam) {
                $output->writeln(sprintf('[%d] %s', $index, $recommendedTeam->getName()));
            }
            $output->writeln('[*] Import as new');
            $answer = $helper->ask($input, $output, new Question("Dude what's the right team?"));
            if (trim($answer) !== '*' && is_numeric($answer)) {
                $this->importService->addTeamMapping($importableTeam, $recommendedTeams[$answer]);
            }
        }
        $this->transactionWrapper->transactional(function () {
            $this->importService->import(new User('import@example.com', '123456', 'bla', 'blubb'));
        });
    }

}