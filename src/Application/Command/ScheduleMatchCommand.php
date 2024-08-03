<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;

class ScheduleMatchCommand implements CommandInterface
{
    private string $matchId;
    private ?DateTimeImmutable $kickoff;
    private ?string $matchDayId;

    /**
     * @param string $matchId
     * @param DateTimeImmutable|null $kickoff
     * @param string|null $matchDayId
     */
    public function __construct(string $matchId, ?DateTimeImmutable $kickoff = null, ?string $matchDayId = null)
    {
        $this->matchId = $matchId;
        $this->kickoff = $kickoff;
        $this->matchDayId = $matchDayId;
    }

    /**
     * @return string
     */
    public function getMatchId(): string
    {
        return $this->matchId;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getKickoff(): ?DateTimeImmutable
    {
        return $this->kickoff;
    }

    /**
     * @return string|null
     */
    public function getMatchDayId(): ?string
    {
        return $this->matchDayId;
    }
}
