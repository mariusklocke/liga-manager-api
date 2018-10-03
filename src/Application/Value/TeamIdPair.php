<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Value;

use HexagonalPlayground\Application\Exception\InvalidInputException;

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
    public function __construct(string $homeTeamId, string $guestTeamId)
    {
        $this->homeTeamId = $homeTeamId;
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
     * @throws InvalidInputException
     */
    public static function fromArray(array $array): self
    {
        $homeTeamId  = $array['home_team_id'] ?? null;
        $guestTeamId = $array['guest_team_id'] ?? null;
        if (!is_string($homeTeamId)) {
            throw new InvalidInputException('Invalid team pair. Missing or invalid property "home_team_id"');
        }
        if (!is_string($guestTeamId)) {
            throw new InvalidInputException('Invalid team pair. Missing or invalid property "guest_team_id"');
        }

        return new self($homeTeamId, $guestTeamId);
    }
}