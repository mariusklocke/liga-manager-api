<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class QueryAction implements ActionInterface
{
    private ContainerInterface $container;
    private JsonResponseWriter $responseWriter;
    private RequestParser $requestParser;

    /**
     * @param ContainerInterface $container
     * @param JsonResponseWriter $responseWriter
     * @param RequestParser $requestParser
     */
    public function __construct(ContainerInterface $container, JsonResponseWriter $responseWriter, RequestParser $requestParser)
    {
        $this->container = $container;
        $this->responseWriter = $responseWriter;
        $this->requestParser = $requestParser;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $this->requestParser->parseJson($request);
        $query = $parsedBody['query'] ?? null;
        $variables = $parsedBody['variables'] ?? [];

        TypeAssert::assertString($query, 'query');
        TypeAssert::assertArray($variables, 'variables');

        $request = $request->withParsedBody($parsedBody);
        $context = new AppContext($request, $this->container);
        $errorHandler = new ErrorHandler($this->container->get(LoggerInterface::class), $request);
        $schema = $this->container->get(Schema::class);

        $result = GraphQL::executeQuery($schema, $query, null, $context, $variables)
            ->setErrorsHandler($errorHandler);

        $response = $response->withStatus(count($result->errors) ? 400 : 200);

        return $this->responseWriter->write($response, $result->toArray());
    }
}
