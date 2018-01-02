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

    /** @var Match[] */
    private $matches;

    /** @var Ranking */
    private $ranking;

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
        return $this->teams->toArray();
    }

    public function hasStarted() : bool
    {
        return ($this->ranking !== null);
    }

    public function hasMatches() : bool
    {
        return count($this->matches) > 0;
    }

    public function start(callable $collectionFactory)
    {
        if ($this->hasStarted()) {
            return false;
        }
        if (!$this->hasMatches()) {
            return false;
        }

        $this->ranking = new Ranking($this, $collectionFactory);
        return true;
    }
}
