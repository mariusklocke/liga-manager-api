<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class HttpTest extends TestCase
{
    /** @var ClientInterface */
    protected $client;

    /** @var JsonResponseParser */
    protected $parser;

    /** @var RequestAuthenticator */
    protected $authenticator;

    /** @var ServerRequestFactoryInterface */
    protected $requestFactory;

    /** @var RequestHandlerInterface */
    private static $app;

    protected function setUp(): void
    {
        if (null === self::$app) {
            self::$app = Bootstrap::bootstrap();
        }

        $this->client = new PsrSlimClient(self::$app);
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
