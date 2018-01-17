<?php

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Repository\MatchRepository;
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
        $match = $this->matchRepository->findMatchById($matchId);
        if ($match === null) {
            return new Response(404);
        }
        return (new Response(200))->withJson($match);
    }

    /**
     * @param string $seasonId
     * @param int    $matchDay
     * @return Response
     */
    public function findMatches(string $seasonId, int $matchDay) : Response
    {
        return (new Response(200))->withJson($this->matchRepository->findMatches($seasonId, $matchDay));
    }
}