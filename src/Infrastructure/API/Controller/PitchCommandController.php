<?php

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Domain\GeographicLocation;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Slim\Http\Request;
use Slim\Http\Response;

class PitchCommandController extends CommandController
{
    /**
     * @param Request $request
     * @return Response
     * @throws BadRequestException
     */
    public function create(Request $request) : Response
    {
        $latitude = $request->getParsedBodyParam('location_latitude');
        $longitude = $request->getParsedBodyParam('location_longitude');
        if (!is_float($latitude) && !is_int($latitude)) {
            throw new BadRequestException(sprintf(
                'Invalid parameter "location_latitude". Number expected. %s given',
                gettype($latitude)
            ));
        }
        if (!is_float($longitude) && !is_int($longitude)) {
            throw new BadRequestException(sprintf(
                'Invalid parameter "location_longitude". Number expected. %s given',
                gettype($longitude)
            ));
        }

        $label = $request->getParsedBodyParam('label');
        if (!is_string($label) || mb_strlen($label) < 1 || mb_strlen($label) > 255) {
            throw new BadRequestException('Invalid label: Has to be a string between 1 and 255 chars');
        }
        $location = new GeographicLocation($longitude, $latitude);
        $command  = new CreatePitchCommand($label, $location);
        $id = $this->commandBus->execute($command);

        return (new Response(200))->withJson(['id' => $id]);
    }
}