<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupLogoCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:logo:cleanup');
        $this->setDescription('Cleanup logos not referenced by a team');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var TeamLogoRepository $teamLogoRepository */
        $teamLogoRepository = $this->container->get(TeamLogoRepository::class);
        /** @var TeamRepositoryInterface $teamRepository */
        $teamRepository = $this->container->get(TeamRepositoryInterface::class);
        /** @var string[] $referencedLogoIds */
        $referencedLogoIds = [];
        $count = 0;

        /** @var Team $team */
        foreach ($teamRepository->findAll() as $team) {
            if ($team->getLogoId() !== null) {
                $referencedLogoIds[] = $team->getLogoId();
            }
        }

        /** @var string $logoId */
        foreach ($teamLogoRepository->findAll() as $logoId) {
            if (array_search($logoId, $referencedLogoIds) === false) {
                $teamLogoRepository->delete($logoId);
                $count++;
            }
        }

        $message = $count > 0 ? "Deleted $count unused logos" : "No unused logos found";

        $this->getStyledIO($input, $output)->success($message);

        return 0;
    }
}
