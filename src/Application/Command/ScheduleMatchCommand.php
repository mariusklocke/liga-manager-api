<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;

class ScheduleMatchCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchId;
    /** @var DateTimeImmutable */
    private $kickoff;

    public function __construct(string $matchId, DateTimeImmutable $kickoff)
    {
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
