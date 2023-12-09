<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;

class TeamHydrator extends Hydrator
{
    private TeamLogoRepository $teamLogoRepository;

    public function __construct(iterable $fields, TeamLogoRepository $teamLogoRepository)
    {
        parent::__construct($fields);
        $this->teamLogoRepository = $teamLogoRepository;
    }

    protected function hydrate(array $row): array
    {
        $team = parent::hydrate($row);
        if ($team['logo_id'] !== null) {
            $team['logo_path'] = $this->teamLogoRepository->generatePublicPath($team['logo_id']);
        }

        return $team;
    }
}
