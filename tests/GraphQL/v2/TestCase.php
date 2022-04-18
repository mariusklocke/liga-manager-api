<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL\v2;

use DateTimeInterface;
use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Tests\Framework\GraphQL\Auth;
use HexagonalPlayground\Tests\Framework\GraphQL\BasicAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\BearerAuth;
use HexagonalPlayground\Tests\Framework\GraphQL\Query;
use HexagonalPlayground\Tests\Framework\JsonResponseParser;
use HexagonalPlayground\Tests\Framework\PsrSlimClient;
use Iterator;
use JsonSerializable;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Slim\App;
use stdClass;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private JsonResponseParser $responseParser;

    protected BasicAuth $defaultAdminAuth;

    private static ?App $app = null;

    protected function setUp(): void
    {
        if (null === self::$app) {
            self::$app = Bootstrap::bootstrap();
        }
        $this->requestFactory = new Psr17Factory();
        $this->client = new PsrSlimClient(self::$app);
        $this->responseParser = new JsonResponseParser();
        $this->defaultAdminAuth = new BasicAuth(
            Environment::get('ADMIN_EMAIL'),
            Environment::get('ADMIN_PASSWORD')
        );
    }

    private function buildRequest(JsonSerializable $payload, ?Auth $auth = null): RequestInterface
    {
        $request = $this->requestFactory->createServerRequest('POST', '/api/graphql/v2')
            ->withBody(Stream::create(json_encode($payload)))
            ->withHeader('Content-Type', 'application/json');

        if ($auth !== null) {
            $request = $request->withHeader('Authorization', $auth->encode());
        }

        return $request;
    }

    protected function request(JsonSerializable $payload, ?Auth $auth = null): stdClass
    {
        $request = $this->buildRequest($payload, $auth);

        $response = $this->client->sendRequest($request);

        return $this->responseParser->parse($response);
    }

    protected function authenticate(BasicAuth $basicAuth): BearerAuth
    {
        $query = $this->createQuery('user')->fields(['id']);

        $request = $this->buildRequest($query, $basicAuth);

        $response = $this->client->sendRequest($request);

        $token = $response->getHeader('X-Token')[0] ?? null;

        if (null === $token) {
            throw new \RuntimeException('Failed to authenticate');
        }

        return new BearerAuth($token);
    }

    /**
     * @param Query $query
     * @param int $pageSize
     * @param Auth|null $auth
     * @return Iterator|array[]
     */
    protected function paginate(Query $query, int $pageSize = 100, ?Auth $auth = null): Iterator
    {
        $offset = 0;
        do {
            $payload = $query
                ->argTypes(['pagination' => 'Pagination'])
                ->argValues(['pagination' => ['limit' => $pageSize, 'offset' => $offset]]);

            $response = $this->request($payload, $auth);

            self::assertObjectNotHasAttribute('errors', $response);
            self::assertObjectHasAttribute('data', $response);

            $result = current(get_object_vars($response->data));
            self::assertIsArray($result);

            if (count($result) > 0) {
                yield $result;
            }

            $offset += $pageSize;

        } while (count($result) > 0);
    }

    protected static function formatDate(DateTimeInterface $dateTime): string
    {
        return $dateTime->format(DATE_ATOM);
    }

    protected static function assertResponseNotHasError(stdClass $response): void
    {
        $hasErrors = isset($response->errors) && count($response->errors) > 0;
        $message   = $hasErrors ? json_encode($response->errors) : '';

        self::assertObjectNotHasAttribute('errors', $response, $message);
    }

    protected function createQuery(string $name): Query
    {
        return new Query($name);
    }
}
