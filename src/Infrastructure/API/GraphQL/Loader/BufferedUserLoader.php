<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;

class BufferedUserLoader
{
    private UserRepository $userRepository;

    private array $byTeamId = [];

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function addTeam(string $teamId): void
    {
        $this->byTeamId[$teamId] = null;
    }

    public function getByTeam(string $teamId): array
    {
        $teamIds = array_keys($this->byTeamId, null, true);

        if (count($teamIds)) {
            $user = $this->userRepository->findByTeamIds($teamIds);

            foreach ($teamIds as $teamId) {
                $this->byTeamId[$teamId] = $user[$teamId] ?? [];
            }
        }

        return $this->byTeamId[$teamId];
    }
}
