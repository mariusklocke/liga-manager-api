<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Filter\EventFilter;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\Persistence\Read\EventRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class EventQueryController
{
    use ResponseFactoryTrait;

    /** @var EventRepository */
    private $repository;

    /**
     * @param EventRepository $repository
     */
    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $id
     * @return ResponseInterface
     */
    public function findEventById(string $id): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findEventById($id));
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function findLatestEvents(Request $request): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findLatestEvents(new EventFilter(
            $request->getQueryParam('start_date'),
            $request->getQueryParam('end_date'),
            $request->getQueryParam('type')
        )));
    }
}