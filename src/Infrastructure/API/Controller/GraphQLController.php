<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryType;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

class GraphQLController
{
    use ResponseFactoryTrait;

    public function query(Request $request, ContainerInterface $container)
    {
        $schema = new Schema([
            'query'      => new QueryType()
        ]);
        $query  = $request->getParsedBodyParam('query');
        $variables = (array) $request->getParsedBodyParam('variables');
        $result = GraphQL::executeQuery($schema, $query, null, $container, $variables);

        return $this->createResponse(200, $result->toArray(true));
    }
}
