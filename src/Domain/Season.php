<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Util\Assert;

class Season extends Competition
{
    const STATE_PREPARATION = 'preparation';
    const STATE_PROGRESS = 'progress';
    const STATE_ENDED = 'ended';

    /** @var Collection */
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
        parent::__construct($id);
        Assert::minLength($name, 1, "A season's name cannot be blank");
        Assert::maxLength($name, 255, "A season's name cannot exceed 255 characters");
        $this->name = $name;
        $this->teams = new ArrayCollection();
        $this->matchDays = new ArrayCollection();
        $this->state = self::STATE_PREPARATION;
        $this->teamCount = 0;
        $this->updateMatchDayCount();

        Publisher::getInstance()->publish(new Event('season:created', [
            'seasonId' => $this->id
        ]));
    }

    /**
     * @param Team $team
     */
    public function addTeam(Team $team): void
    {
        Assert::false($this->hasStarted(), 'Cannot add teams to season which has already started');
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $this->teamCount++;
        }
    }

    /**
     * @param Team $team
     */
    public function removeTeam(Team $team): void
    {
        Assert::false($this->hasStarted(), 'Cannot remove teams from a season which has already started');
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            $this->teamCount--;
        }
    }

    /**
     * @return Team[]
     */
    public function getTeams() : array
    {
        return $this->teams->toArray();
    }

    /**
     * Removes all teams from season
     */
    public function clearTeams(): void
    {
        Assert::false($this->hasStarted(), 'Cannot remove teams from a season which has already started');
        $this->teams->clear();
        $this->teamCount = 0;
    }

    /**
     * Removes all match days and their matches from season
     */
    public function clearMatchDays(): void
    {
        Assert::false($this->hasStarted(), 'Cannot remove matches from a season which has already started');
        foreach ($this->matchDays as $matchDay) {
            /** @var MatchDay $matchDay */
            $matchDay->clearMatches();
        }
        $this->matchDays->clear();
        $this->updateMatchDayCount();
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
     */
    public function start(): void
    {
        Assert::false($this->hasStarted(), 'Cannot start a season which has already been started');
        Assert::true($this->hasMatches(), 'Cannot start a season which has no matches');
        $this->ranking = new Ranking($this);
        $this->state = self::STATE_PROGRESS;

        Publisher::getInstance()->publish(new Event('season:started', [
            'seasonId' => $this->id
        ]));
    }

    /**
     * Finalizes the season
     */
    public function end(): void
    {
        Assert::true($this->hasStarted(), 'Cannot end a season which has not been started');
        $this->state = self::STATE_ENDED;

        Publisher::getInstance()->publish(new Event('season:ended', [
            'seasonId' => $this->id
        ]));
    }

    /**
     * @param string|null $id
     * @param int $number
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @return MatchDay
     */
    public function createMatchDay(?string $id, int $number, DateTimeImmutable $startDate, DateTimeImmutable $endDate): MatchDay
    {
        $matchDay = parent::createMatchDay($id, $number, $startDate, $endDate);
        $this->updateMatchDayCount();
        return $matchDay;
    }

    /**
     * @return Ranking
     */
    public function getRanking(): Ranking
    {
        Assert::false(
            $this->ranking === null,
            'Cannot access ranking for a season which has not been started'
        );
        return $this->ranking;
    }

    /**
     * @return MatchDay[]
     */
    public function getMatchDays(): array
    {
        return $this->matchDays->toArray();
    }

    private function updateMatchDayCount(): void
    {
        $this->matchDayCount = $this->matchDays->count();
    }
}
