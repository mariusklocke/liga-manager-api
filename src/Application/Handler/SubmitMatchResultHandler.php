<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchResult;
use HexagonalPlayground\Domain\User;

class SubmitMatchResultHandler
{
    /** @var OrmRepositoryInterface */
    private $matchRepository;
    /** @var Authenticator */
    private $authenticator;

    /**
     * @param OrmRepositoryInterface $matchRepository
     * @param Authenticator $authenticator
     */
    public function __construct(OrmRepositoryInterface $matchRepository, Authenticator $authenticator)
    {
        $this->matchRepository = $matchRepository;
        $this->authenticator = $authenticator;
    }

    /**
     * @param SubmitMatchResultCommand $command
     */
    public function handle(SubmitMatchResultCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $user  = $this->authenticator->getAuthenticatedUser();
        $this->checkPermissions($match, $user);
        $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
        $match->submitResult($result, $user);
    }

    /**
     * @param Match $match
     * @param User $user
     * @throws PermissionException
     */
    private function checkPermissions(Match $match, User $user)
    {
        if ($user->hasRole(User::ROLE_ADMIN) || $user->isInTeam($match->getHomeTeam()) || $user->isInTeam($match->getGuestTeam())) {
            return;
        }

        throw new PermissionException('User is not permitted to submit results for this match');
    }
}
