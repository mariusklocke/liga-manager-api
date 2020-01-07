<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;
use HexagonalPlayground\Application\TypeAssert;

class ScheduleMatchCommand implements CommandInterface
{
    /** @var string */
    private $matchId;
    /** @var DateTimeImmutable */
    private $kickoff;

    /**
     * @param string $matchId
     * @param DateTimeImmutable $kickoff
     */
    public function __construct($matchId, DateTimeImmutable $kickoff)
    {
        TypeAssert::assertString($matchId, 'matchId');
        $this->matchId = $matchId;
        $this->kickoff = $kickoff;
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->matchId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getKickoff(): DateTimeImmutable
    {
        return $this->kickoff;
    }
}
