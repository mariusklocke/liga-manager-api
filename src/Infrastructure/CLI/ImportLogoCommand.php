<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Nyholm\Psr7\UploadedFile;
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
        /** @var FilesystemService $filesystemService */
        $filesystemService = $this->container->get(FilesystemService::class);
        /** @var TeamLogoRepository $teamLogoRepository */
        $teamLogoRepository = $this->container->get(TeamLogoRepository::class);
        /** @var TeamRepositoryInterface $teamRepository */
        $teamRepository = $this->container->get(TeamRepositoryInterface::class);
        /** @var Team $team */
        $team = $teamRepository->find($input->getArgument('teamId'));

        $filePath = $input->getArgument('file');
        $stream = $filesystemService->openFile($filePath, 'r');
        $uploadedFile = new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK);
        $logoId = $teamLogoRepository->save($uploadedFile);
        $team->setLogoId($logoId);
        $teamRepository->save($team);
        $filesystemService->deleteFile($filePath);
        $filePath = $teamLogoRepository->generatePrivatePath($logoId);

        $this->getStyledIO($input, $output)->success("Team logo has been imported to $filePath");

        return 0;
    }
}
