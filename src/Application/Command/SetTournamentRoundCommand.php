<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\Value\DatePeriod;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\Value\TeamIdPair;

class SetTournamentRoundCommand implements CommandInterface
{
    /** @var string */
    private $tournamentId;

    /** @var int */
    private $round;

    /** @var TeamIdPair[] */
    private $teamIdPairs;

    /** @var DatePeriod */
    private $datePeriod;

    public function __construct(string $tournamentId, int $round, array $teamIdPairs, DatePeriod $datePeriod)
    {
        if (empty($teamIdPairs)) {
            throw new InvalidInputException('Team pairs cannot be empty');
        }

        if (count($teamIdPairs) > 64) {
            throw new InvalidInputException('Request exceeds maximum amount of 64 team pairs.');
        }

        $this->tournamentId = $tournamentId;
        $this->round        = $round;
        $this->datePeriod   = $datePeriod;
        $this->teamIdPairs  = [];

        foreach ($teamIdPairs as $pair) {
            if (!($pair instanceof TeamIdPair)) {
                throw new InvalidInputException(sprintf(
                    'Invalid type for team pair. Expected: %s. Given: %s',
                    TeamIdPair::class,
                    get_class($pair)
                ));
            }
            $this->teamIdPairs[] = $pair;
        }
    }

    /**
     * @return TeamIdPair[]
     */
    public function getTeamIdPairs(): array
    {
        return $this->teamIdPairs;
    }

    /**
     * @return string
     */
    public function getTournamentId(): string
    {
        return $this->tournamentId;
    }

    /**
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}