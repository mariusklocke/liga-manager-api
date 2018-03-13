<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;

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
        if ($this->matchResult !== null) {
            $this->season->revertResult($this->homeTeam->getId(), $this->guestTeam->getId(), $this->matchResult);
        }
        $this->season->addResult($this->homeTeam->getId(), $this->guestTeam->getId(), $matchResult);
        $this->matchResult = $matchResult;
        return $this;
    }

    /**
     * @param DateTimeImmutable $kickoff
     * @return Match
     */
    public function schedule(DateTimeImmutable $kickoff) : Match
    {
        $this->kickoff = $kickoff;
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

    /**
     * @return int
     */
    public function getMatchDay(): int
    {
        return $this->matchDay;
    }
}
