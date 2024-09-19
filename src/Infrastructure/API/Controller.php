<?php declare(strict_types=1);
namespace HexagonalPlayground\Infrastructure\API;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpMethodNotAllowedException;

abstract class Controller implements RequestHandlerInterface
{
    use ResponseBuilderTrait;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        switch ($request->getMethod()) {
            case 'GET':
                return $this->get($request);
            case 'POST':
                return $this->post($request);
            case 'DELETE':
                return $this->delete($request);
        }
        throw new HttpMethodNotAllowedException($request);
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        throw new HttpMethodNotAllowedException($request);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        throw new HttpMethodNotAllowedException($request);
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        throw new HttpMethodNotAllowedException($request);
    }
}
