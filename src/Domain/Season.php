<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Season extends Competition
{
    const STATE_PREPARATION = 'preparation';
    const STATE_PROGRESS = 'progress';
    const STATE_ENDED = 'ended';

    /** @var Collection|Team[] */
    private $teams;

    /** @var Ranking|null */
    private $ranking;

    /** @var string */
    private $state;

    /** @var int */
    private $matchDayCount;

    /** @var int */
    private $teamCount;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->state = self::STATE_PREPARATION;
        $this->matchDayCount = 0;
        $this->teamCount = 0;
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
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $this->teamCount++;
        }
        return $this;
    }

    /**
     * @param Team $team
     * @return Season
     * @throws DomainException
     */
    public function removeTeam(Team $team) : Season
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot remove team from season which has already started');
        }
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            $this->teamCount--;
        }
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
        $this->teamCount = 0;
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
        $this->matchDayCount = max($this->matchDayCount, $match->getMatchDay());
        return $this;
    }

    /**
     * @return Season
     */
    public function clearMatches() : Season
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot remove matches from a season which has already started');
        }

        $this->matches->clear();
        $this->matchDayCount = 0;
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
     * @return Season
     * @throws DomainException
     */
    public function start()
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot start a season which has already been started');
        }
        if (!$this->hasMatches()) {
            throw new DomainException('Cannot start a season which has no matches');
        }

        $this->ranking = new Ranking($this);
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
    }

    /**
     * @param string $homeTeamId
     * @param string $guestTeamId
     * @param MatchResult $result
     * @throws DomainException
     */
    public function revertResult(string $homeTeamId, string $guestTeamId, MatchResult $result)
    {
        if (!$this->isInProgress()) {
            throw new DomainException('Cannot revert a result from a season which is not in progress');
        }
        $this->ranking->revertResult($homeTeamId, $guestTeamId, $result);
    }

    /**
     * @return bool
     */
    public function hasSecondHalf() : bool
    {
        return false;
    }
}
