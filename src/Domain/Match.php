<?php

namespace HexagonalDream\Domain;

use DateTimeImmutable;
use HexagonalDream\Domain\Exception\MatchSchedulingException;
use HexagonalDream\Domain\Exception\TeamDidNotParticipateException;

class Match
{
    /** @var string */
    private $id;

    /** @var Season */
    private $season;

    /** @var int */
    private $matchDay;

    /** @var Team */
    private $homeTeam;

    /** @var Team */
    private $guestTeam;

    /** @var MatchResult */
    private $matchResult;

    /** @var DateTimeImmutable */
    private $kickoff;

    /** @var Pitch */
    private $pitch;

    /** @var DateTimeImmutable */
    private $cancelledAt;

    /**
     * Create a new match
     *
     * @param UuidGeneratorInterface $uuidGenerator
     * @param Season                 $season
     * @param int                    $matchDay
     * @param Team                   $homeTeam
     * @param Team                   $guestTeam
     */
    public function __construct(UuidGeneratorInterface $uuidGenerator, Season $season, int $matchDay, Team $homeTeam, Team $guestTeam)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->season = $season;
        $this->matchDay = $matchDay;
        $this->homeTeam = $homeTeam;
        $this->guestTeam = $guestTeam;
    }

    /**
     * @param MatchResult $matchResult
     * @return Match
     */
    public function submitResult(MatchResult $matchResult) : Match
    {
        $this->matchResult = $matchResult;
        return $this;
    }

    /**
     * @param DateTimeImmutable $kickoff
     * @return Match
     * @throws MatchSchedulingException
     */
    public function schedule(DateTimeImmutable $kickoff) : Match
    {
        $now = new DateTimeImmutable();
        if ($kickoff < $now) {
            throw new MatchSchedulingException('Cannot schedule matches in the past');
        }
        $this->kickoff = $kickoff;
        return $this;
    }

    /**
     * @return Match
     */
    public function cancel() : Match
    {
        $this->matchResult = null;
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
        return $this;
    }

    public function isRemovable() : bool
    {
        return ($this->cancelledAt === null && $this->matchResult === null);
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
     * @param Team $team
     * @return int
     * @throws TeamDidNotParticipateException
     */
    public function getScoredGoalsBy(Team $team) : int
    {
        if ($team->equals($this->homeTeam)) {
            return $this->matchResult->getHomeScore();
        }
        if ($team->equals($this->guestTeam)) {
            return $this->matchResult->getGuestScore();
        }

        throw new TeamDidNotParticipateException();
    }

    /**
     * @param Team $team
     * @return int
     * @throws TeamDidNotParticipateException
     */
    public function getConcededGoalsBy(Team $team) : int
    {
        if ($team->equals($this->homeTeam)) {
            return $this->matchResult->getGuestScore();
        }
        if ($team->equals($this->guestTeam)) {
            return $this->matchResult->getHomeScore();
        }

        throw new TeamDidNotParticipateException();
    }

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
        $this->pitch = null;
        $this->cancelledAt = null;
    }

    public function rematch(UuidGeneratorInterface $uuidGenerator, int $matchDay) : Match
    {
        $clone = clone $this;
        $clone->id = $uuidGenerator->generateUuid();
        $clone->matchDay = $matchDay;
        $clone->homeTeam = $this->guestTeam;
        $clone->guestTeam = $this->homeTeam;
        return $clone;
    }
}
