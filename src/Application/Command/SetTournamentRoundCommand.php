<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Application\Value\DatePeriod;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\Value\TeamIdPair;

class SetTournamentRoundCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $tournamentId;

    /** @var int */
    private $round;

    /** @var TeamIdPair[] */
    private $teamIdPairs;

    /** @var DatePeriod */
    private $datePeriod;

    /**
     * @param string $tournamentId
     * @param int $round
     * @param array $teamIdPairs
     * @param array $datePeriod
     */
    public function __construct($tournamentId, $round, $teamIdPairs, $datePeriod)
    {
        TypeAssert::assertString($tournamentId, 'tournamentId');
        TypeAssert::assertInteger($round, 'round');
        TypeAssert::assertArray($teamIdPairs, 'teamIdPairs');
        TypeAssert::assertArray($datePeriod, 'datePeriod');

        if (empty($teamIdPairs)) {
            throw new InvalidInputException('Team pairs cannot be empty');
        }

        if (count($teamIdPairs) > 64) {
            throw new InvalidInputException('Request exceeds maximum amount of 64 team pairs.');
        }

        $this->tournamentId = $tournamentId;
        $this->round        = $round;
        $this->datePeriod   = InputParser::parseDatePeriod($datePeriod);
        $this->teamIdPairs  = [];

        foreach ($teamIdPairs as $index => $pair) {
            TypeAssert::assertArray($pair, 'teamIdPairs[' . $index . ']');
            $this->teamIdPairs[] = TeamIdPair::fromArray($pair);
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