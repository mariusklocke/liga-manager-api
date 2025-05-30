<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class HttpTest extends TestCase
{
    protected ClientInterface $client;

    protected JsonResponseParser $parser;

    protected RequestAuthenticator $authenticator;

    protected ServerRequestFactoryInterface $requestFactory;

    protected OpenApiValidator $schemaValidator;

    protected function setUp(): void
    {
        $this->client = Container::getInstance()->get(ClientInterface::class);
        $this->requestFactory = Container::getInstance()->get(ServerRequestFactoryInterface::class);
        $this->parser = Container::getInstance()->get(JsonResponseParser::class);
        $this->authenticator = Container::getInstance()->get(RequestAuthenticator::class);
        $this->schemaValidator = Container::getInstance()->get(OpenApiValidator::class);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     * @return ServerRequestInterface
     */
    protected function createRequest(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $request = $this->requestFactory->createServerRequest($method, $uri);

        if (!empty($data)) {
            $request->getBody()->write(json_encode($data));
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        return $request;
    }

    /**
     * Sends a request to the application and validates the response against the OpenAPI schema.
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function sendRequest(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->client->sendRequest($request);

        $this->schemaValidator->validateResponse($request, $response);

        return $response;
    }
}
