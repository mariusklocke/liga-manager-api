<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class CreateMatchCommand implements CommandInterface
{
    use IdAware;

    /** @var string */
    private string $matchDayId;

    /** @var string */
    private string $homeTeamId;

    /** @var string */
    private string $guestTeamId;

    /**
     * @param string|null $id
     * @param string $matchDayId
     * @param string $homeTeamId
     * @param string $guestTeamId
     */
    public function __construct(?string $id, string $matchDayId, string $homeTeamId, string $guestTeamId)
    {
        $this->setId($id);
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