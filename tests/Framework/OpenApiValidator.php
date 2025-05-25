<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OpenApiValidator
{
    private ResponseValidator $responseValidator;

    public function __construct()
    {
        $this->responseValidator = (new ValidatorBuilder)->fromYamlFile($this->getYamlPath())->getResponseValidator();
    }

    public function validateResponse(RequestInterface $request, ResponseInterface $response): void
    {
        $this->responseValidator->validate($this->buildOperation($request), $response);
    }

    private function getYamlPath(): string
    {
        return join(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'openapi.yml']);
    }

    private function buildOperation(RequestInterface $request): OperationAddress
    {
        return new OperationAddress($request->getUri()->getPath(), strtolower($request->getMethod()));
    }
}
