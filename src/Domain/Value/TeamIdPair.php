<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

class TeamIdPair extends ValueObject
{
    /** @var string */
    private $homeTeamId;

    /** @var string */
    private $guestTeamId;

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     */
    public function __construct(string $homeTeamId, string $guestTeamId)
    {
        $this->homeTeamId  = $homeTeamId;
        $this->guestTeamId = $guestTeamId;
    }

    /**
     * @return string
     */
    public function getHomeTeamId(): string
    {
        return $this->homeTeamId;
    }

    /**
     * @return string
     */
    public function getGuestTeamId(): string
    {
        return $this->guestTeamId;
    }
}
