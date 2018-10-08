<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Filter\MatchFilter;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use Slim\Http\Request;
use Slim\Http\Response;

class MatchQueryController
{
    /** @var MatchRepository */
    private $matchRepository;

    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param string $matchId
     * @return Response
     */
    public function findMatchById(string $matchId) : Response
    {
        return (new Response(200))->withJson($this->matchRepository->findMatchById($matchId));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function findMatches(Request $request): Response
    {
        $filter = new MatchFilter(
            $request->getQueryParam('season_id'),
            $request->getQueryParam('tournament_id'),
            $request->getQueryParam('match_day_id'),
            $request->getQueryParam('team_id')
        );
        return (new Response(200))->withJson(
            $this->matchRepository->findMatches($filter)
        );
    }
}