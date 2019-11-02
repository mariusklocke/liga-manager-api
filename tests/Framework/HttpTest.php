<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Uri;

abstract class HttpTest extends TestCase
{
    /** @var ClientInterface */
    protected $client;

    /** @var JsonResponseParser */
    protected $parser;

    /** @var RequestAuthenticator */
    protected $authenticator;

    /** @var Environment */
    private static $environment;

    /** @var App */
    private static $app;

    public static function setUpBeforeClass(): void
    {
        if (null === self::$app) {
            self::$app = Bootstrap::bootstrap();
        }
        if (null === self::$environment) {
            self::$environment = Environment::mock();
        }
    }

    protected function setUp(): void
    {
        $this->client = new PsrSlimClient(self::$app);
        $this->parser = new JsonResponseParser();
        $this->authenticator = new RequestAuthenticator();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     * @return ServerRequestInterface
     */
    protected function createRequest(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $body = new Body(fopen('php://temp', 'r+'));
        if (!empty($data)) {
            $body->write(json_encode($data));
        }

        return new Request(
            $method,
            Uri::createFromString($uri),
            new Headers(['Content-Type' => 'application/json']),
            [],
            self::$environment->all(),
            $body
        );
    }
}
