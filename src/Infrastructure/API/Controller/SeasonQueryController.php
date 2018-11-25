<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use Psr\Http\Message\ResponseInterface;

class SeasonQueryController
{
    use ResponseFactoryTrait;

    /** @var SeasonRepository */
    private $seasonRepository;

    public function __construct(SeasonRepository $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @return ResponseInterface
     */
    public function findAllSeasons(): ResponseInterface
    {
        return $this->createResponse(200, $this->seasonRepository->findAllSeasons());
    }

    /**
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function findSeasonById(string $seasonId): ResponseInterface
    {
        return $this->createResponse(200, $this->seasonRepository->findSeasonById($seasonId));
    }

    /**
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function findRanking(string $seasonId): ResponseInterface
    {
        return $this->createResponse(200, $this->seasonRepository->findRanking($seasonId));
    }

    /**
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function findMatchDays(string $seasonId): ResponseInterface
    {
        return $this->createResponse(200, $this->seasonRepository->findMatchDays($seasonId));
    }
}