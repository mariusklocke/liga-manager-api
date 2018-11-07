<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use Slim\Http\Response;

class SeasonQueryController
{
    /** @var SeasonRepository */
    private $seasonRepository;

    public function __construct(SeasonRepository $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
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
        return (new Response(200))->withJson($this->seasonRepository->findSeasonById($seasonId));
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function findRanking(string $seasonId) : Response
    {
        return (new Response(200))->withJson($this->seasonRepository->findRanking($seasonId));
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function findMatchDays(string $seasonId): Response
    {
        return (new Response())->withJson($this->seasonRepository->findMatchDays($seasonId));
    }
}