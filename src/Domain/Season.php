<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;

class Season extends Competition
{
    const STATE_PREPARATION = 'preparation';
    const STATE_PROGRESS = 'progress';
    const STATE_ENDED = 'ended';

    /** @var Collection */
    private Collection $teams;

    /** @var Ranking|null */
    private ?Ranking $ranking = null;

    /** @var string */
    private string $state;

    /** @var int */
    private int $matchDayCount;

    /** @var int */
    private int $teamCount;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        parent::__construct($id);
        // TODO: This should become a InvalidInputException
        Assert::true(StringUtils::length($name) > 0, "A season's name cannot be blank");
        // TODO: This should become a InvalidInputException
        Assert::true(StringUtils::length($name) <= 255, "A season's name cannot exceed 255 characters");
        $this->name = $name;
        $this->teams = new ArrayCollection();
        $this->matchDays = new ArrayCollection();
        $this->state = self::STATE_PREPARATION;
        $this->teamCount = 0;
        $this->updateMatchDayCount();
    }

    /**
     * @param Team $team
     * @return bool
     */
    public function hasTeam(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    /**
     * @param Team $team
     */
    public function addTeam(Team $team): void
    {
        // TODO: This should become a ConflictException
        Assert::false($this->hasStarted(), 'Cannot add teams to season which has already started');
        if (!$this->hasTeam($team)) {
            $this->teams[] = $team;
            $this->teamCount++;
        }
    }

    /**
     * @param Team $team
     */
    public function removeTeam(Team $team): void
    {
        // TODO: This should become a ConflictException
        Assert::false($this->hasStarted(), 'Cannot remove teams from a season which has already started');
        if ($this->hasTeam($team)) {
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
        // TODO: This should become a ConflictException
        Assert::false($this->hasStarted(), 'Cannot remove teams from a season which has already started');
        $this->teams->clear();
        $this->teamCount = 0;
    }

    /**
     * Removes all match days and their matches from season
     */
    public function clearMatchDays(): void
    {
        // TODO: This should become a ConflictException
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
        // TODO: This should become a ConflictException
        Assert::false($this->hasStarted(), 'Cannot start a season which has already been started');
        // TODO: This should become a ConflictException
        Assert::true($this->hasMatches(), 'Cannot start a season which has no matches');
        $this->ranking = new Ranking($this);
        $this->state = self::STATE_PROGRESS;
    }

    /**
     * Finalizes the season
     */
    public function end(): void
    {
        // TODO: This should become a ConflictException
        Assert::true($this->hasStarted(), 'Cannot end a season which has not been started');
        $this->state = self::STATE_ENDED;
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
        // TODO: This should become a ConflictException
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

    /**
     * Replaces a team by another team
     *
     * @param Team $from
     * @param Team $to
     */
    public function replaceTeam(Team $from, Team $to): void
    {
        // TODO: This should become a ConflictException
        Assert::true($this->hasTeam($from), 'Cannot replace a team which is not part of season');
        // TODO: This should become a ConflictException
        Assert::false($this->hasTeam($to), 'Cannot replace a team with a team which is already part of season');

        foreach ($this->matchDays as $matchDay) {
            foreach ($matchDay->getMatches() as $match) {
                if ($match->getHomeTeam()->equals($from)) {
                    $match->setHomeTeam($to);
                }

                if ($match->getGuestTeam()->equals($from)) {
                    $match->setGuestTeam($to);
                }
            }
        }

        if ($this->ranking) {
            $this->ranking->replaceTeam($from, $to);
        }

        $this->teams->removeElement($from);
        $this->teams->add($to);
    }

    private function updateMatchDayCount(): void
    {
        $this->matchDayCount = $this->matchDays->count();
    }
}
