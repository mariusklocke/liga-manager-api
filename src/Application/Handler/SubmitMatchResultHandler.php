<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Exception\AuthorizationException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchResult;

class SubmitMatchResultHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var Authenticator */
    private $authenticator;

    public function __construct(ObjectPersistenceInterface $persistence, Authenticator $authenticator)
    {
        $this->persistence = $persistence;
        $this->authenticator = $authenticator;
    }

    public function handle(SubmitMatchResultCommand $command)
    {
        /** @var Match $match */
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        $user  = $this->authenticator->getAuthenticatedUser();
        if ($user->isInTeam($match->getHomeTeam()) || $user->isInTeam($match->getGuestTeam())) {
            $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
            $match->submitResult($result);
            return;
        }

        throw new AuthorizationException('User is not authorized to submit results for this match');
    }
}
