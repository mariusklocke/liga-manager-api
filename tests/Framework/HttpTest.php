<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use GuzzleHttp\Client;
use HexagonalPlayground\Infrastructure\API\Application;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class HttpTest extends TestCase
{
    protected ClientInterface $client;

    protected JsonResponseParser $parser;

    protected RequestAuthenticator $authenticator;

    protected ServerRequestFactoryInterface $requestFactory;

    private static ?RequestHandlerInterface $app = null;

    protected function setUp(): void
    {
        $baseUrl = getenv('APP_BASE_URL');
        if ($baseUrl) {
            $this->client = new Client(['base_uri' => $baseUrl]);
        } else {
            if (null === self::$app) {
                self::$app = new Application();
            }
            $this->client = new PsrSlimClient(self::$app);
        }
        $this->parser = new JsonResponseParser();
        $this->authenticator = new RequestAuthenticator();
        $this->requestFactory = new Psr17Factory();
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
}
