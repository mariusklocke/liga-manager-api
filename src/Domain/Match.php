<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Event\MatchCancelled;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\MatchLocated;
use HexagonalPlayground\Domain\Event\MatchResultSubmitted;
use HexagonalPlayground\Domain\Event\MatchScheduled;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\MatchResult;

class Match extends Entity
{
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

    /** @var Pitch|null */
    private $pitch;

    /** @var DateTimeImmutable|null */
    private $cancelledAt;

    /** @var string|null */
    private $cancellationReason;

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
        Assert::false($homeTeam->equals($guestTeam), 'A team cannot play against itself');
        $this->matchDay = $matchDay;
        $this->homeTeam = $homeTeam;
        $this->guestTeam = $guestTeam;
    }

    /**
     * @param MatchResult $matchResult
     * @param User $user
     */
    public function submitResult(MatchResult $matchResult, User $user)
    {
        if (null !== $this->matchResult && $this->matchResult->equals($matchResult)) {
            return;
        }

        $this->setResult($matchResult);
        $this->cancelledAt = null;
        $this->cancellationReason = null;
        Publisher::getInstance()->publish(MatchResultSubmitted::create(
            $this->id,
            $matchResult->getHomeScore(),
            $matchResult->getGuestScore(),
            $user->getId()
        ));
    }

    /**
     * @param DateTimeImmutable $kickoff
     */
    public function schedule(DateTimeImmutable $kickoff): void
    {
        $this->kickoff = $kickoff;
        Publisher::getInstance()->publish(MatchScheduled::create($this->id, $kickoff));
    }

    /**
     * @param string $reason
     */
    public function cancel(string $reason): void
    {
        Assert::maxLength($reason, 255, 'Cancellation reason exceeds maximum length of 255');
        $previousResult = $this->matchResult;
        $this->setResult(null);
        $this->cancelledAt = new DateTimeImmutable();
        $this->cancellationReason = $reason;
        Publisher::getInstance()->publish(MatchCancelled::create(
            $this->id,
            $this->cancellationReason,
            $previousResult
        ));
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
        Publisher::getInstance()->publish(MatchLocated::create($this->id, $pitch->getId()));
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
     * @return bool
     */
    public function hasResult(): bool
    {
        return (null !== $this->matchResult);
    }

    /**
     * @param MatchResult|null $matchResult
     */
    private function setResult(?MatchResult $matchResult): void
    {
        if ($this->matchResult !== null) {
            $this->matchDay->revertResult($this->homeTeam->getId(), $this->guestTeam->getId(), $this->matchResult);
        }
        if ($matchResult !== null) {
            $this->matchDay->addResult($this->homeTeam->getId(), $this->guestTeam->getId(), $matchResult);
        }
        $this->matchResult = $matchResult;
    }

    /**
     * @return MatchDay
     */
    public function getMatchDay(): MatchDay
    {
        return $this->matchDay;
    }
}
