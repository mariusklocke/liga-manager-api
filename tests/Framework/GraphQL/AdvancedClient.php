<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\Query;
use HexagonalPlayground\Tests\Framework\GraphQL\Query\v2\User;
use HexagonalPlayground\Tests\Framework\JsonResponseParser;
use HexagonalPlayground\Tests\Framework\PsrSlimClient;
use Iterator;
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

    /**
     * Sends a query or mutation request
     *
     * @param NamedOperation $payload
     * @param Auth|null $auth
     * @return stdClass|array|null
     * @throws Exception
     */
    public function request(NamedOperation $payload, ?Auth $auth = null)
    {
        $operationName = $payload->getName();
        $request = $this->buildRequest($payload, $auth);
        $response = $this->httpClient->sendRequest($request);
        $parsedResponse = $this->responseParser->parse($response);

        if (isset($parsedResponse->errors) && count($parsedResponse->errors) > 0) {
            throw new Exception($parsedResponse->errors);
        }

        if (!isset($parsedResponse->data)) {
            throw new Exception(['Empty response']);
        }

        return $parsedResponse->data->$operationName ?? null;
    }

    /**
     * Retrieve a bearer token for a given set of credentials
     *
     * @param BasicAuth $basicAuth
     * @return BearerAuth
     * @throws Exception
     */
    public function authenticate(BasicAuth $basicAuth): BearerAuth
    {
        $query = new User();
        $request = $this->buildRequest($query, $basicAuth);
        $response = $this->httpClient->sendRequest($request);
        $token = current($response->getHeader('X-Token'));

        if (!is_string($token)) {
            throw new Exception(['Failed to authenticate']);
        }

        return new BearerAuth($token);
    }

    /**
     * Executes a query with pagination
     *
     * @param Query $query
     * @param Auth|null $auth
     * @param int $pageSize
     * @return Iterator|array[]
     */
    public function paginate(Query $query, ?Auth $auth = null, int $pageSize = 100): Iterator
    {
        $offset = 0;
        do {
            $payload = $query
                ->withArgTypes(['pagination' => 'Pagination'])
                ->withArgValues(['pagination' => ['limit' => $pageSize, 'offset' => $offset]]);
            $result = $this->request($payload, $auth);

            if (!is_array($result)) {
                throw new Exception(['Response does not contain data for pagination']);
            }

            if (count($result) > 0) {
                yield $result;
            }

            $offset += $pageSize;

        } while (count($result) > 0);
    }

    /**
     * @param JsonSerializable $payload
     * @param Auth|null $auth
     * @return RequestInterface
     */
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
