<?php

namespace HexagonalDream\Domain;

use DateTimeImmutable;

class Season
{
    const STATE_PREPARATION = 'preparation';
    const STATE_PROGRESS = 'progress';
    const STATE_ENDED = 'ended';

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

    /** @var Ranking|null */
    private $ranking;

    /** @var string */
    private $state;

    public function __construct(UuidGeneratorInterface $uuidGenerator, string $name)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->name = $name;
        $this->teams = [];
        $this->state = self::STATE_PREPARATION;
    }

    /**
     * @param Team $team
     * @return Season
     * @throws DomainException
     */
    public function addTeam(Team $team) : Season
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot add teams to season which has already started');
        }
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

    /**
     * @return Season
     */
    public function clearTeams() : Season
    {
        $this->teams->clear();
        return $this;
    }

    /**
     * @param Match $match
     * @return Season
     * @throws DomainException
     */
    public function addMatch(Match $match) : Season
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot add matches to season which has already started');
        }
        $this->matches[] = $match;
        return $this;
    }

    /**
     * @return Season
     */
    public function clearMatches() : Season
    {
        $this->matches->clear();
        return $this;
    }

    /**
     * @return bool
     */
    public function hasStarted() : bool
    {
        return ($this->ranking !== null);
    }

    /**
     * @return bool
     */
    public function hasMatches() : bool
    {
        return count($this->matches) > 0;
    }

    /**
     * Initializes the season ranking
     *
     * @param callable $collectionFactory
     * @return Season
     * @throws DomainException
     */
    public function start(callable $collectionFactory)
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot start a season which has already started');
        }
        if (!$this->hasMatches()) {
            throw new DomainException('Cannot start a season which has no matches');
        }

        $this->ranking = new Ranking($this, $collectionFactory);
        $this->state = self::STATE_PROGRESS;
        return $this;
    }

    /**
     * @return Season
     */
    public function end() : Season
    {
        $this->state = self::STATE_ENDED;
        return $this;
    }
}
