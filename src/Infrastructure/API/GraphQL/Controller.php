<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\JsonEncoder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    /** @var AppContext */
    private $appContext;

    /** @var Schema */
    private $schema;

    /** @var ErrorHandler */
    private $errorHandler;

    /** @var JsonEncoder */
    private $jsonEncoder;

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
        $this->jsonEncoder = new JsonEncoder();
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

        return $this->jsonEncoder->encode($response, $result->toArray());
    }
}
