<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;

class BufferedTournamentLoader
{
    private TournamentRepository $tournamentRepository;

    private array $byTournamentId = [];

    public function __construct(TournamentRepository $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
    }

    public function addTournamentId(string $tournamentId): void
    {
        $this->byTournamentId[$tournamentId] = null;
    }

    public function getByTournament(string $tournamentId): ?array
    {
        $tournamentIds = array_keys($this->byTournamentId, null, true);

        if (count($tournamentIds)) {
            $filter = new EqualityFilter(
                'id',
                Filter::MODE_INCLUDE,
                $tournamentIds
            );

            foreach ($this->tournamentRepository->findMany([$filter]) as $tournament) {
                $this->byTournamentId[$tournament['id']] = $tournament;
            }
        }

        return $this->byTournamentId[$tournamentId] ?? null;
    }
}
