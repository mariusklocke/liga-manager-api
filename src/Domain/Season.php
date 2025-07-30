<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;

class Season extends Competition
{
    /** @var Collection */
    private Collection $teams;

    /** @var Ranking|null */
    private ?Ranking $ranking = null;

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
        Assert::true(
            StringUtils::length($name) > 0,
            InvalidInputException::class,
            'seasonNameCannotBeBlank'
        );
        Assert::true(
            StringUtils::length($name) <= 255,
            InvalidInputException::class,
            'seasonNameExceedsMaxLength',
            [255]
        );
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
        !$this->hasStarted() || throw new ConflictException('seasonHasAlreadyStarted');
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
        !$this->hasStarted() || throw new ConflictException('seasonHasAlreadyStarted');
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
        !$this->hasStarted() || throw new ConflictException('seasonHasAlreadyStarted');
        $this->teams->clear();
        $this->teamCount = 0;
    }

    /**
     * Removes all match days and their matches from season
     */
    public function clearMatchDays(): void
    {
        !$this->hasStarted() || throw new ConflictException('seasonHasAlreadyStarted');
        foreach ($this->matchDays as $matchDay) {
            /** @var MatchDay $matchDay */
            $matchDay->clearMatches();
        }
        $this->matchDays->clear();
        $this->updateMatchDayCount();
    }

    /**
     * Initializes the season ranking
     */
    public function start(): void
    {
        parent::start();
        $this->ranking = new Ranking($this);
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
        return $this->ranking ?? throw new ConflictException('competitionNotStarted');
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
        $this->hasTeam($from) || throw new ConflictException('teamNotParticipatingInSeason');
        !$this->hasTeam($to) || throw new ConflictException('teamAlreadyParticipatingInSeason');

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
