<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Value;

use HexagonalPlayground\Application\TypeAssert;

class TeamIdPair
{
    /** @var string */
    private $homeTeamId;

    /** @var string */
    private $guestTeamId;

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     */
    public function __construct($homeTeamId, $guestTeamId)
    {
        TypeAssert::assertString($homeTeamId, 'home_team_id');
        TypeAssert::assertString($guestTeamId, 'guest_team_id');
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

    /**
     * @param array $array
     * @return TeamIdPair
     */
    public static function fromArray(array $array): self
    {
        $homeTeamId  = $array['home_team_id'] ?? null;
        $guestTeamId = $array['guest_team_id'] ?? null;

        return new self($homeTeamId, $guestTeamId);
    }
}