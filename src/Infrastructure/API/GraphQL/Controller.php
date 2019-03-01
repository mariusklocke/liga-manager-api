<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

class Controller
{
    use ResponseFactoryTrait;

    public function query(Request $request, ContainerInterface $container)
    {
        $schema = new Schema([
            'query'    => new QueryType(),
            'mutation' => new MutationType()
        ]);
        $query = $request->getParsedBodyParam('query');
        $variables = (array)$request->getParsedBodyParam('variables');
        $context = new AppContext($request, $container);
        $errorHandler = new ErrorHandler($container->get('logger'), $context);

        $result = GraphQL::executeQuery($schema, $query, null, $context, $variables)
            ->setErrorsHandler($errorHandler);

        return $this->createResponse(200, $result->toArray());
    }
}
