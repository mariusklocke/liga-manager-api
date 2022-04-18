<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class CreateMatchCommand extends CreateCommand implements CommandInterface
{
    /** @var string */
    private string $matchDayId;

    /** @var string */
    private string $homeTeamId;

    /** @var string */
    private string $guestTeamId;

    /**
     * @param string $id
     * @param string $matchDayId
     * @param string $homeTeamId
     * @param string $guestTeamId
     */
    public function __construct(string $id, string $matchDayId, string $homeTeamId, string $guestTeamId)
    {
        $this->id = $id;
        $this->matchDayId = $matchDayId;
        $this->homeTeamId = $homeTeamId;
        $this->guestTeamId = $guestTeamId;
    }

    /**
     * @return string
     */
    public function getMatchDayId(): string
    {
        return $this->matchDayId;
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
