<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class QueryAction implements ActionInterface
{
    /** @var ContainerInterface */
    private ContainerInterface $container;

    /** @var JsonResponseWriter */
    private JsonResponseWriter $responseWriter;

    /**
     * @param ContainerInterface $container
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(ContainerInterface $container, JsonResponseWriter $responseWriter)
    {
        $this->container = $container;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $query = $parsedBody['query'] ?? null;
        $variables = $parsedBody['variables'] ?? [];

        TypeAssert::assertString($query, 'query');
        TypeAssert::assertArray($variables, 'variables');

        $context = new AppContext($request, $this->container);
        $errorHandler = new ErrorHandler($this->container->get(LoggerInterface::class), $request);
        $schema = $this->container->get(Schema::class);

        $result = GraphQL::executeQuery($schema, $query, null, $context, $variables)
            ->setErrorsHandler($errorHandler);

        $response = $response->withStatus(count($result->errors) ? 400 : 200);

        return $this->responseWriter->write($response, $result->toArray());
    }
}