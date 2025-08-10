<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedLoaderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Controller extends BaseController
{
    private Schema $schema;
    private ContainerInterface $container;

    public function __construct(ResponseFactoryInterface $responseFactory, Schema $schema, ContainerInterface $container)
    {
        parent::__construct($responseFactory);
        $this->schema = $schema;
        $this->container = $container;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildTextResponse(SchemaPrinter::doPrint($this->schema));
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $this->parseJson($request);
        $query = $parsedBody['query'] ?? null;
        $variables = $parsedBody['variables'] ?? [];

        is_string($query) || throw new InvalidInputException('invalidDataType', ['query', 'string']);
        is_array($variables) || throw new InvalidInputException('invalidDataType', ['variables', 'array']);

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
