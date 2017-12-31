<?php
/**
 * PitchQueryController.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Repository\PitchRepository;
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