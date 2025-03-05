<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportLogoCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:logo:import');
        $this->setDescription('Import a logo (source file will be deleted)');
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to logo file (WEBP)');
        $this->addArgument('teamId', InputArgument::REQUIRED, 'Team ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var TeamLogoRepository $teamLogoRepository */
        $teamLogoRepository = $this->container->get(TeamLogoRepository::class);
        /** @var TeamRepositoryInterface $teamRepository */
        $teamRepository = $this->container->get(TeamRepositoryInterface::class);
        /** @var UploadedFileFactoryInterface $uploadedFileFactory */
        $uploadedFileFactory = $this->container->get(UploadedFileFactoryInterface::class);
        /** @var Team $team */
        $team = $teamRepository->find($input->getArgument('teamId'));

        $inputFile = new File($input->getArgument('file'));
        $stream = $inputFile->open('r');
        $uploadedFile = $uploadedFileFactory->createUploadedFile($stream, $stream->getSize());
        $logoId = $teamLogoRepository->save($uploadedFile);
        $team->setLogoId($logoId);
        $teamRepository->save($team);
        $teamRepository->flush();
        $inputFile->delete();
        $filePath = $teamLogoRepository->getStorageFile($logoId)->getPath();

        $this->getStyledIO($input, $output)->success("Team logo has been imported to $filePath");

        return 0;
    }
}
