<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class PitchCommandController extends CommandController
{
    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function create(Request $request): ResponseInterface
    {
        $latitude  = $request->getParsedBodyParam('location_latitude');
        $longitude = $request->getParsedBodyParam('location_longitude');
        $label     = $request->getParsedBodyParam('label');
        $this->assertNumber('location_latitude', $latitude);
        $this->assertNumber('location_longitude', $longitude);
        $this->assertString('label', $label);

        $command = new CreatePitchCommand($label, (float)$longitude, (float)$latitude);
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param string $pitchId
     * @param Request $request
     * @return ResponseInterface
     */
    public function updateContact(string $pitchId, Request $request): ResponseInterface
    {
        $firstName = $request->getParsedBodyParam('first_name');
        $lastName  = $request->getParsedBodyParam('last_name');
        $phone     = $request->getParsedBodyParam('phone');
        $email     = $request->getParsedBodyParam('email');

        $this->assertString('first_name', $firstName);
        $this->assertString('last_name', $lastName);
        $this->assertString('phone', $phone);
        $this->assertString('email', $email);

        $command = new UpdatePitchContactCommand($pitchId, $firstName, $lastName, $phone, $email);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}