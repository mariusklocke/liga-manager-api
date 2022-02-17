<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use DateTimeImmutable;

class MatchAppointment extends ValueObject
{
    /** @var DateTimeImmutable */
    private DateTimeImmutable $kickoff;

    /** @var string[] */
    private array $unavailableTeamIds;

    /** @var string */
    private string $pitchId;

    /**
     * @param DateTimeImmutable $kickoff
     * @param string[] $unavailableTeamIds
     * @param string $pitchId
     */
    public function __construct(DateTimeImmutable $kickoff, array $unavailableTeamIds, string $pitchId)
    {
        $this->kickoff = $kickoff;
        $this->unavailableTeamIds = $unavailableTeamIds;
        $this->pitchId = $pitchId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getKickoff(): DateTimeImmutable
    {
        return $this->kickoff;
    }

    /**
     * @return string[]
     */
    public function getUnavailableTeamIds(): array
    {
        return $this->unavailableTeamIds;
    }

    /**
     * @return string
     */
    public function getPitchId(): string
    {
        return $this->pitchId;
    }
}
