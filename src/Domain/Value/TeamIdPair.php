<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

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
}
