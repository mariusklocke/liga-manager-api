<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;
use Psr\Http\Message\ResponseInterface;

class PitchQueryController
{
    use ResponseFactoryTrait;

    /** @var PitchRepository */
    private $pitchRepository;

    public function __construct(PitchRepository $pitchRepository)
    {
        $this->pitchRepository = $pitchRepository;
    }

    /**
     * @param string $pitchId
     * @return ResponseInterface
     */
    public function findPitchById(string $pitchId): ResponseInterface
    {
        return $this->createResponse(200, $this->pitchRepository->findPitchById($pitchId));
    }

    /**
     * @return ResponseInterface
     */
    public function findAllPitches(): ResponseInterface
    {
        return $this->createResponse(200, $this->pitchRepository->findAllPitches());
    }
}