<?php
/**
 * ControllerProvider.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API;

use HexagonalDream\Infrastructure\API\Controller\TeamActionController;
use HexagonalDream\Infrastructure\API\Controller\TeamQueryController;
use Slim\App;

class ControllerProvider
{
    public function registerRoutes(App $app)
    {
        $container = $app->getContainer();
        $app->get('/team/', function () use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container['infrastructure.api.controller.TeamQueryController'];
            return $controller->findAllTeams();
        });
        $app->get('/team/{id}', function ($request, $response, $args) use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container['infrastructure.api.controller.TeamQueryController'];
            return $controller->findTeamById($args['id']);
        });
        $app->get('/season/{id}/team/', function ($request, $response, $args) use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container['infrastructure.api.controller.TeamQueryController'];
            return $controller->findTeamsBySeasonId($args['id']);
        });
        $app->post('/team/', function ($request) use ($container) {
            /** @var TeamActionController $controller */
            $controller = $container['infrastructure.api.controller.TeamActionController'];
            return $controller->create($request);
        });
        $app->delete('/team/{id}', function ($request, $response, $args) use ($container) {
            /** @var TeamActionController $controller */
            $controller = $container['infrastructure.api.controller.TeamActionController'];
            return $controller->delete($args['id']);
        });
    }
}