<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;
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
        return (new Response(200))->withJson($this->pitchRepository->findPitchById($pitchId));
    }

    /**
     * @return Response
     */
    public function findAllPitches() : Response
    {
        return (new Response(200))->withJson($this->pitchRepository->findAllPitches());
    }
}