<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchResult;
use HexagonalPlayground\Domain\User;

class SubmitMatchResultHandler
{
    /** @var OrmRepositoryInterface */
    private $matchRepository;

    /**
     * @param OrmRepositoryInterface $matchRepository
     */
    public function __construct(OrmRepositoryInterface $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param SubmitMatchResultCommand $command
     */
    public function __invoke(SubmitMatchResultCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $this->checkPermissions($match, $command);
        $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
        $match->submitResult($result, $command->getAuthenticatedUser());
    }

    /**
     * @param Match $match
     * @param SubmitMatchResultCommand $command
     * @throws PermissionException
     */
    private function checkPermissions(Match $match, SubmitMatchResultCommand $command): void
    {
        if ($command->getAuthenticatedUser()->hasRole(User::ROLE_ADMIN)
            || $command->getAuthenticatedUser()->isInTeam($match->getHomeTeam())
            || $command->getAuthenticatedUser()->isInTeam($match->getGuestTeam())
        ) {
            return;
        }

        throw new PermissionException($command->getAuthenticatedUser()->getEmail() . ' is not permitted to submit results for this match');
    }
}
