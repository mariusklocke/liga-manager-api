<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use DateTimeImmutable;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Application\TypeAssert;

class ScheduleMatchCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchId;
    /** @var DateTimeImmutable */
    private $kickoff;

    /**
     * @param string $matchId
     * @param string $kickoff
     */
    public function __construct($matchId, $kickoff)
    {
        TypeAssert::assertString($matchId, 'matchId');
        TypeAssert::assertString($kickoff, 'kickoff');
        $this->matchId = $matchId;
        $this->kickoff = InputParser::parseDateTime($kickoff);
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
