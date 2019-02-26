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
        $command = new CreatePitchCommand(
            $request->getParsedBodyParam('id'),
            $request->getParsedBodyParam('label'),
            $request->getParsedBodyParam('location_longitude'),
            $request->getParsedBodyParam('location_latitude')
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(200, ['id' => $command->getId()]);
    }

    /**
     * @param string $pitchId
     * @param Request $request
     * @return ResponseInterface
     */
    public function updateContact(string $pitchId, Request $request): ResponseInterface
    {
        $command = new UpdatePitchContactCommand(
            $pitchId,
            $request->getParsedBodyParam('first_name'),
            $request->getParsedBodyParam('last_name'),
            $request->getParsedBodyParam('phone'),
            $request->getParsedBodyParam('email')
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}