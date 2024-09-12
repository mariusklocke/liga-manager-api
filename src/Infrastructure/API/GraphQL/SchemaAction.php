<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SchemaAction implements ActionInterface
{
    private ResponseSerializer $responseSerializer;
    private Schema $schema;

    public function __construct(ResponseSerializer $responseSerializer, Schema $schema)
    {
        $this->responseSerializer = $responseSerializer;
        $this->schema = $schema;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responseSerializer->serializeText($response, SchemaPrinter::doPrint($this->schema));
    }
}
