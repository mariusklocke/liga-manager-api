<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\Persistence\Read\RankingRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use Slim\Http\Response;

class SeasonQueryController
{
    /** @var SeasonRepository */
    private $seasonRepository;
    /** @var RankingRepository */
    private $rankingRepository;

    public function __construct(SeasonRepository $seasonRepository, RankingRepository $rankingRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->rankingRepository = $rankingRepository;
    }

    /**
     * @return Response
     */
    public function findAllSeasons() : Response
    {
        return (new Response(200))->withJson($this->seasonRepository->findAllSeasons());
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function findSeasonById(string $seasonId) : Response
    {
        $season = $this->seasonRepository->findSeasonById($seasonId);
        if (null === $season) {
            return new Response(404);
        }
        return (new Response(200))->withJson($season);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function findRanking(string $seasonId) : Response
    {
        $ranking = $this->rankingRepository->findRanking($seasonId);
        if (null === $ranking) {
            return new Response(404);
        }

        $ranking['positions'] = $this->rankingRepository->findRankingPositions($seasonId);
        return (new Response(200))->withJson($ranking);
    }
}