<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\Uuid;

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
     * @param string $name
     */
    public function __construct(string $name)
    {
        Assert::minLength($name, 1, "A season's name cannot be blank");
        Assert::maxLength($name, 255, "A season's name cannot exceed 255 characters");
        $this->id = Uuid::create();
        $this->name = $name;
        $this->teams = new ArrayCollection();
        $this->matchDays = new ArrayCollection();
        $this->state = self::STATE_PREPARATION;
        $this->teamCount = 0;
        $this->matchDayCount = 0;
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
     * @return Season
     */
    public function clearMatchDays() : Season
    {
        if ($this->hasStarted()) {
            throw new DomainException('Cannot remove matches from a season which has already started');
        }

        foreach ($this->matchDays as $matchDay) {
            $matchDay->clearMatches();
        }
        $this->matchDays->clear();
        $this->matchDayCount = 0;

        return $this;
    }

    /**
     * @return bool
     */
    private function hasStarted() : bool
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
    private function hasMatches() : bool
    {
        return $this->matchDays->count() > 0;
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
     * @param MatchDay $matchDay
     */
    public function addMatchDay(MatchDay $matchDay)
    {
        $this->matchDays[] = $matchDay;
        $this->matchDayCount = $this->matchDays->count();
    }

    /**
     * @return Match[]
     */
    public function getMatches(): array
    {
        $matches = [];
        foreach ($this->matchDays as $matchDay) {
            /** @var MatchDay $matchDay */
            $matches = array_merge($matches, $matchDay->getMatches());
        }
        return $matches;
    }
}
