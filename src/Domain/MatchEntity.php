<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Domain\Value\MatchResult;

class MatchEntity extends Entity
{
    /** @var MatchDay */
    private MatchDay $matchDay;

    /** @var Team */
    private Team $homeTeam;

    /** @var Team */
    private Team $guestTeam;

    /** @var MatchResult|null */
    private ?MatchResult $matchResult = null;

    /** @var DateTimeImmutable|null */
    private ?DateTimeImmutable $kickoff = null;

    /** @var Pitch|null */
    private ?Pitch $pitch = null;

    /** @var DateTimeImmutable|null */
    private ?DateTimeImmutable $cancelledAt = null;

    /** @var string|null */
    private ?string $cancellationReason = null;

    /**
     * Create a new match
     *
     * @param string|null $id
     * @param MatchDay $matchDay
     * @param Team $homeTeam
     * @param Team $guestTeam
     */
    public function __construct(?string $id, MatchDay $matchDay, Team $homeTeam, Team $guestTeam)
    {
        parent::__construct($id);
        Assert::false(
            $homeTeam->equals($guestTeam),
            InvalidInputException::class,
            'teamCannotPlayAgainstIfself'
        );
        $this->matchDay = $matchDay;
        $this->setHomeTeam($homeTeam);
        $this->setGuestTeam($guestTeam);
    }

    /**
     * @param MatchResult $matchResult
     */
    public function submitResult(MatchResult $matchResult): void
    {
        $this->matchDay->getCompetition()->isInProgress() || throw new ConflictException('competitionNotInProgress');

        if ($this->hasResult()) {
            if ($this->matchResult->equals($matchResult)) {
                return;
            }
            $this->matchDay->revertResult($this->homeTeam->getId(), $this->guestTeam->getId(), $this->matchResult);
        }

        $this->matchDay->addResult($this->homeTeam->getId(), $this->guestTeam->getId(), $matchResult);
        $this->matchResult = $matchResult;

        $this->cancelledAt = null;
        $this->cancellationReason = null;
    }

    /**
     * @param DateTimeImmutable $kickoff
     */
    public function setKickoff(DateTimeImmutable $kickoff): void
    {
        $this->kickoff = $kickoff;
    }

    /**
     * @param MatchDay $matchDay
     */
    public function setMatchDay(MatchDay $matchDay): void
    {
        $this->matchDay = $matchDay;
    }

    /**
     * @param string $reason
     */
    public function cancel(string $reason): void
    {
        $this->matchDay->getCompetition()->isInProgress() || throw new ConflictException('competitionNotInProgress');

        Assert::true(
            StringUtils::length($reason) <= 255,
            InvalidInputException::class,
            'cancellationReasonExceedsMaxLength',
            [255]
        );

        if ($this->hasResult()) {
            $this->matchDay->revertResult($this->homeTeam->getId(), $this->guestTeam->getId(), $this->matchResult);
        }
        $this->matchResult = null;

        $this->cancelledAt = new DateTimeImmutable();
        $this->cancellationReason = $reason;
    }

    /**
     * @param Pitch $pitch
     */
    public function locate(Pitch $pitch): void
    {
        if ($this->pitch !== null && $this->pitch->equals($pitch)) {
            return;
        }

        if ($this->pitch !== null) {
            $this->pitch->removeMatch($this);
        }

        $pitch->addMatch($this);
        $this->pitch = $pitch;
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
     * @param Team $homeTeam
     */
    public function setHomeTeam(Team $homeTeam): void
    {
        $this->homeTeam = $homeTeam;
    }

    /**
     * @param Team $guestTeam
     */
    public function setGuestTeam(Team $guestTeam): void
    {
        $this->guestTeam = $guestTeam;
    }

    /**
     * @return bool
     */
    public function hasResult(): bool
    {
        return (null !== $this->matchResult);
    }

    /**
     * @return MatchResult|null
     */
    public function getMatchResult(): ?MatchResult
    {
        return $this->matchResult;
    }

    /**
     * @return string|null
     */
    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getKickoff(): ?DateTimeImmutable
    {
        return $this->kickoff;
    }

    /**
     * @return MatchDay
     */
    public function getMatchDay(): MatchDay
    {
        return $this->matchDay;
    }
}
