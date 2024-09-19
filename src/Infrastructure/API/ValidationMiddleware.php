<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    private ServerRequestValidator $requestValidator;

    public function __construct(ServerRequestValidator $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            try {
                $this->requestValidator->validate($request);
            } catch (ValidationFailed $e) {
                throw new InvalidInputException($e->getMessage());
            }
        }
        return $handler->handle($request);
    }
}
