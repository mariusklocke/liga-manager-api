<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use HexagonalPlayground\Infrastructure\API\Application;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class HttpTest extends TestCase
{
    protected ClientInterface $client;

    protected JsonResponseParser $parser;

    protected RequestAuthenticator $authenticator;

    protected ServerRequestFactoryInterface $requestFactory;

    protected OpenApiValidator $schemaValidator;

    private static ?RequestHandlerInterface $app = null;

    protected function setUp(): void
    {
        if (!extension_loaded('xdebug')) {
            $this->client = new Client(['base_uri' => getenv('APP_BASE_URL')]);
            $this->requestFactory = new HttpFactory();
        } else {
            if (null === self::$app) {
                self::$app = new Application();
            }
            $this->client = new PsrSlimClient(self::$app);
            $this->requestFactory = new Psr17Factory();
        }
        $this->parser = new JsonResponseParser();
        $this->authenticator = new RequestAuthenticator();
        $this->schemaValidator = new OpenApiValidator();
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
