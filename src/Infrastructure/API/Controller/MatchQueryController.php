<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use DateTimeImmutable;
use Exception;
use HexagonalPlayground\Application\Repository\MatchRepository;
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
        $match = $this->matchRepository->findMatchById($matchId);
        if ($match === null) {
            return new Response(404);
        }
        return (new Response(200))->withJson($match);
    }

    /**
     * @param string  $seasonId
     * @param Request $request
     * @return Response
     */
    public function findMatchesInSeason(string $seasonId, Request $request) : Response
    {
        if (($matchDay = $request->getQueryParam('match_day')) !== null) {
            return (new Response(200))->withJson($this->matchRepository->findMatchesByMatchDay($seasonId, (int) $matchDay));
        }
        if (($teamId = $request->getQueryParam('team_id')) !== null) {
            return (new Response(200))->withJson($this->matchRepository->findMatchesByTeam($seasonId, $teamId));
        }
        if (
            ($from = $request->getQueryParam('from')) !== null &&
            ($to = $request->getQueryParam('to')) !== null
        ) {
            try {
                $from = new DateTimeImmutable($from);
                $to = new DateTimeImmutable($to);
            } catch (Exception $e) {
                return (new Response(400))->withJson($e->getMessage());
            }
            return (new Response(200))->withJson($this->matchRepository->findMatchesByDate($seasonId, $from, $to));
        }

        return (new Response(400))->withJson('Missing query parameter');
    }

    /**
     * @param string $tournamentId
     * @return Response
     */
    public function findMatchesInTournament(string $tournamentId) : Response
    {
        return (new Response(200))->withJson($this->matchRepository->findMatchesInTournament($tournamentId));
    }
}