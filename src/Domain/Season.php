<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class Season
{
    const STATE_PREPARATION = 'preparation';
    const STATE_PROGRESS = 'progress';
    const STATE_ENDED = 'ended';

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var CollectionInterface|Team[] */
    private $teams;

    /** @var CollectionInterface|Match[] */
    private $matches;

    /** @var Ranking|null */
    private $ranking;

    /** @var string */
    private $state;

    public function __construct(UuidGeneratorInterface $uuidGenerator, string $name, callable $collectionFactory)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->name = $name;
        $this->teams = $collectionFactory();
        $this->matches = $collectionFactory();
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
     * @throws DomainException
     */
    public function clearTeams() : Season
    {
        if ($this->hasStarted()) {
            throw new DomainException("Cannot remove teams from a season which has already started");
        }
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
    public function hasEnded() : bool
    {
        return $this->state === self::STATE_ENDED;
    }

    /**
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->state === self::STATE_PROGRESS;
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
            throw new DomainException('Cannot start a season which has already been started');
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
     * @throws DomainException
     */
    public function end() : Season
    {
        if (!$this->hasStarted()) {
            throw new DomainException("Cannot end a season which hasn't even started");
        }
        $this->state = self::STATE_ENDED;
        return $this;
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $result
     * @throws DomainException
     */
    public function addResult(string $homeTeamId, string $guestTeamId, MatchResult $result)
    {
        if (!$this->isInProgress()) {
            throw new DomainException('Cannot add a result to a season which is not in progress');
        }
        $this->ranking->addResult($homeTeamId, $guestTeamId, $result);
        if ($this->ranking->isFinal()) {
            $this->end();
        }
    }

    /**
     * @return int
     */
    public function getMatchCount(): int
    {
        return count($this->matches);
    }
}
