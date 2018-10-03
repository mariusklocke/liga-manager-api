<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\MatchLocated;
use HexagonalPlayground\Domain\Event\MatchResultSubmitted;
use HexagonalPlayground\Domain\Event\MatchScheduled;
use HexagonalPlayground\Domain\Util\Uuid;

class Match
{
    /** @var string */
    private $id;

    /** @var MatchDay */
    private $matchDay;

    /** @var Team */
    private $homeTeam;

    /** @var Team */
    private $guestTeam;

    /** @var MatchResult|null */
    private $matchResult;

    /** @var DateTimeImmutable|null */
    private $kickoff;

    /** @var DateTimeImmutable|null */
    private $plannedFor;

    /** @var Pitch|null */
    private $pitch;

    /** @var DateTimeImmutable|null */
    private $cancelledAt;

    /**
     * Create a new match
     *
     * @param MatchDay $matchDay
     * @param Team $homeTeam
     * @param Team $guestTeam
     * @throws DomainException If $homeTeam and $guestTeam are equal
     */
    public function __construct(MatchDay $matchDay, Team $homeTeam, Team $guestTeam)
    {
        if ($homeTeam === $guestTeam) {
            throw new DomainException('A team cannot play against itself');
        }

        $this->id = Uuid::create();
        $this->matchDay = $matchDay;
        $this->homeTeam = $homeTeam;
        $this->guestTeam = $guestTeam;
        $this->plannedFor = null;
    }

    /**
     * @param MatchResult $matchResult
     * @param User $user
     * @return Match
     */
    public function submitResult(MatchResult $matchResult, User $user) : Match
    {
        $competition = $this->matchDay->getCompetition();
        if ($competition instanceof Season) {
            if ($this->matchResult !== null) {
                $competition->revertResult($this->homeTeam->getId(), $this->guestTeam->getId(), $this->matchResult);
            }
            $competition->addResult($this->homeTeam->getId(), $this->guestTeam->getId(), $matchResult);
        }
        $this->matchResult = $matchResult;
        Publisher::getInstance()->publish(MatchResultSubmitted::create(
            $this->id,
            $matchResult->getHomeScore(),
            $matchResult->getGuestScore(),
            $user->getId()
        ));
        return $this;
    }

    /**
     * @param DateTimeImmutable $kickoff
     * @return Match
     */
    public function schedule(DateTimeImmutable $kickoff) : Match
    {
        $this->kickoff = $kickoff;
        Publisher::getInstance()->publish(MatchScheduled::create($this->id, $kickoff));
        return $this;
    }

    /**
     * @return Match
     * @throws DomainException
     */
    public function cancel() : Match
    {
        if ($this->matchResult !== null) {
            throw new DomainException('Cannot cancel a match with a submitted result');
        }
        $this->cancelledAt = new DateTimeImmutable();
        return $this;
    }

    /**
     * @param Pitch $pitch
     * @return Match
     */
    public function locate(Pitch $pitch) : Match
    {
        $this->pitch = $pitch;
        Publisher::getInstance()->publish(MatchLocated::create($this->id, $pitch->getId()));
        return $this;
    }

    /**
     * @return Team
     */
    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    /**
     * @return Team
     */
    public function getGuestTeam(): Team
    {
        return $this->guestTeam;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return sprintf('%s - %s', $this->homeTeam->getName(), $this->guestTeam->getName());
    }

    private function __clone()
    {
        $this->id = null;
        $this->matchDay = null;
        $this->matchResult = null;
        $this->kickoff = null;
        $this->plannedFor = null;
        $this->pitch = null;
        $this->cancelledAt = null;
    }

    public function rematch(MatchDay $matchDay) : Match
    {
        $clone = clone $this;
        $clone->id = Uuid::create();
        $clone->matchDay = $matchDay;
        $clone->homeTeam = $this->guestTeam;
        $clone->guestTeam = $this->homeTeam;
        return $clone;
    }

    /**
     * @return MatchDay
     */
    public function getMatchDay(): MatchDay
    {
        return $this->matchDay;
    }
}
