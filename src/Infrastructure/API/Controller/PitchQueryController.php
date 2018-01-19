<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Repository\PitchRepository;
use Slim\Http\Response;

class PitchQueryController
{
    /** @var PitchRepository */
    private $pitchRepository;

    public function __construct(PitchRepository $pitchRepository)
    {
        $this->pitchRepository = $pitchRepository;
    }

    /**
     * @param string $pitchId
     * @return Response
     */
    public function findPitchById(string $pitchId) : Response
    {
        $pitch = $this->pitchRepository->findPitchById($pitchId);
        if (null === $pitch) {
            return new Response(404);
        }

        return (new Response(200))->withJson($pitch);
    }

    /**
     * @return Response
     */
    public function findAllPitches() : Response
    {
        return (new Response(200))->withJson($this->pitchRepository->findAllPitches());
    }
}