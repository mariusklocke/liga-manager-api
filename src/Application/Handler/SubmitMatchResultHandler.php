<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Application\Security\PermissionChecker;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchResult;
use HexagonalPlayground\Domain\User;

class SubmitMatchResultHandler
{
    /** @var OrmRepositoryInterface */
    private $matchRepository;
    /** @var User */
    private $authenticatedUser;
    /** @var PermissionChecker */
    private $permissionChecker;

    /**
     * @param OrmRepositoryInterface $matchRepository
     * @param User $authenticatedUser
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(OrmRepositoryInterface $matchRepository, User $authenticatedUser, PermissionChecker $permissionChecker)
    {
        $this->matchRepository   = $matchRepository;
        $this->authenticatedUser = $authenticatedUser;
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * @param SubmitMatchResultCommand $command
     */
    public function handle(SubmitMatchResultCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $this->permissionChecker->assertCanSubmitResultFor($match);
        $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
        $match->submitResult($result, $this->authenticatedUser);
    }
}
