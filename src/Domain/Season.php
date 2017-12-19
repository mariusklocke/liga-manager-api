<?php

namespace HexagonalDream\Domain;

use DateTimeImmutable;

class Season
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var DateTimeImmutable */
    private $startDate;

    /** @var DateTimeImmutable */
    private $endDate;

    /** @var Team[] */
    private $teams;

    public function __construct(UuidGeneratorInterface $uuidGenerator, string $name)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->name = $name;
        $this->teams = [];
    }

    /**
     * @param Team $team
     * @return Season
     */
    public function addTeam(Team $team) : Season
    {
        $this->teams[] = $team;
        return $this;
    }

    /**
     * @return Team[]
     */
    public function getTeams() : array
    {
        return $this->teams;
    }
}
