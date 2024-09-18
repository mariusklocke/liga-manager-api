<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedLoaderInterface;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Controller extends BaseController
{
    private RequestParser $requestParser;
    private Schema $schema;
    private ContainerInterface $container;

    public function __construct(ResponseFactoryInterface $responseFactory, RequestParser $requestParser, Schema $schema, ContainerInterface $container)
    {
        parent::__construct($responseFactory);
        $this->requestParser = $requestParser;
        $this->schema = $schema;
        $this->container = $container;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildTextResponse(SchemaPrinter::doPrint($this->schema));
    }

    public function post(ServerRequestInterface $request): ResponseInterface
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

        /** @var BufferedLoaderInterface[] $loaders */
        $loaders = $this->container->get(BufferedLoaderInterface::class);
        foreach ($loaders as $loader) {
            $loader->init();
        }

        $result = GraphQL::executeQuery($schema, $query, null, $context, $variables)
            ->setErrorsHandler($errorHandler);

        return $this->buildJsonResponse($result->toArray(), count($result->errors) ? 400 : 200);
    }
}
