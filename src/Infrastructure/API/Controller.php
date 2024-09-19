<?php declare(strict_types=1);
namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Throwable;

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

    /**
     * @param RequestInterface $request
     * @return mixed
     * @throws InvalidInputException
     */
    protected function parseJson(RequestInterface $request): mixed
    {
        if (!in_array('application/json', $request->getHeader('Content-Type'))) {
            throw new InvalidInputException('Missing expected Content-Type header "application/json"');
        }

        try {
            return json_decode((string)$request->getBody(), true, 64, JSON_THROW_ON_ERROR);
        } catch (Throwable $throwable) {
            throw new InvalidInputException('Failed to decode JSON from request body', 0, $throwable);
        }
    }
}
