<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    use JsonEncodingTrait;

    /** @var AppContext */
    private $appContext;

    /** @var Schema */
    private $schema;

    /** @var ErrorHandler */
    private $errorHandler;

    /**
     * @param AppContext $appContext
     * @param Schema $schema
     * @param ErrorHandler $errorHandler
     */
    public function __construct(AppContext $appContext, Schema $schema, ErrorHandler $errorHandler)
    {
        $this->appContext = $appContext;
        $this->schema = $schema;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function query(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $query = $parsedBody['query'] ?? null;
        $variables = $parsedBody['variables'] ?? [];

        TypeAssert::assertString($query, 'query');
        TypeAssert::assertArray($variables, 'variables');

        $result = GraphQL::executeQuery($this->schema, $query, null, $this->appContext, $variables)
            ->setErrorsHandler($this->errorHandler);

        $response = $response->withStatus(count($result->errors) ? 400 : 200);

        return $this->toJson($response, $result->toArray());
    }
}
