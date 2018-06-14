<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
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
        $latitude  = $request->getParsedBodyParam('location_latitude');
        $longitude = $request->getParsedBodyParam('location_longitude');
        $label     = $request->getParsedBodyParam('label');
        $this->assertTypeOneOf('location_latitude', $latitude, ['float', 'integer']);
        $this->assertTypeOneOf('location_longitude', $longitude, ['float', 'integer']);
        $this->assertTypeExact('label', $label, 'string');

        $location = new GeographicLocation((float)$longitude, (float)$latitude);
        $command  = new CreatePitchCommand($label, $location);
        $id = $this->commandBus->execute($command);

        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param string $pitchId
     * @param Request $request
     * @return Response
     */
    public function updateContact(string $pitchId, Request $request): Response
    {
        $firstName = $request->getParsedBodyParam('first_name');
        $lastName  = $request->getParsedBodyParam('last_name');
        $phone     = $request->getParsedBodyParam('phone');
        $email     = $request->getParsedBodyParam('email');

        $this->assertTypeExact('first_name', $firstName, 'string');
        $this->assertTypeExact('last_name', $lastName, 'string');
        $this->assertTypeExact('phone', $phone, 'string');
        $this->assertTypeExact('email', $email, 'string');

        $this->commandBus->execute(new UpdatePitchContactCommand(
            $pitchId,
            $firstName,
            $lastName,
            $phone,
            $email
        ));

        return new Response(204);
    }
}