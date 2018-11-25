<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Filter\MatchFilter;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class MatchQueryController
{
    use ResponseFactoryTrait;

    /** @var MatchRepository */
    private $matchRepository;

    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param string $matchId
     * @return ResponseInterface
     */
    public function findMatchById(string $matchId): ResponseInterface
    {
        return $this->createResponse(200, $this->matchRepository->findMatchById($matchId));
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function findMatches(Request $request): ResponseInterface
    {
        return $this->createResponse(200, $this->matchRepository->findMatches(new MatchFilter(
            $request->getQueryParam('season_id'),
            $request->getQueryParam('tournament_id'),
            $request->getQueryParam('match_day_id'),
            $request->getQueryParam('team_id')
        )));
    }
}