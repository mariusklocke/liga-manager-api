<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Framework\JsonResponseParser;
use HexagonalPlayground\Tests\Framework\PsrSlimClient;
use JsonSerializable;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use stdClass;

class AdvancedClient
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private JsonResponseParser $responseParser;

    public function __construct()
    {
        $this->httpClient = new PsrSlimClient(Bootstrap::bootstrap());
        $this->requestFactory = new Psr17Factory();
        $this->responseParser = new JsonResponseParser();
    }

    public function createQuery(string $name): Query
    {
        return new Query($name);
    }

    public function createMutation(string $name): Mutation
    {
        return new Mutation($name);
    }

    /**
     * @param JsonSerializable $payload
     * @param Auth|null $auth
     * @return stdClass
     * @throws Exception
     */
    public function request(JsonSerializable $payload, ?Auth $auth = null): stdClass
    {
        $request = $this->buildRequest($payload, $auth);
        $response = $this->httpClient->sendRequest($request);
        $parsedResponse = $this->responseParser->parse($response);

        if (isset($parsedResponse->errors) && count($parsedResponse->errors) > 0) {
            throw new Exception($parsedResponse->errors);
        }

        return $parsedResponse;
    }

    /**
     * @param BasicAuth $basicAuth
     * @return BearerAuth
     * @throws Exception
     */
    public function authenticate(BasicAuth $basicAuth): BearerAuth
    {
        $query = $this->createQuery('user')->fields(['id']);
        $request = $this->buildRequest($query, $basicAuth);
        $response = $this->httpClient->sendRequest($request);
        $token = current($response->getHeader('X-Token'));

        if (!is_string($token)) {
            throw new Exception(['Failed to authenticate']);
        }

        return new BearerAuth($token);
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
}
