<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    use ResponseFactoryTrait;

    public function query(ServerRequestInterface $request, ContainerInterface $container)
    {
        $parsedBody = $request->getParsedBody();
        $query = $parsedBody['query'] ?? null;
        $variables = $parsedBody['variables'] ?? null;

        TypeAssert::assertString($query, 'query');
        TypeAssert::assertArray($variables, 'variables');

        $context = new AppContext($request, $container);
        $errorHandler = new ErrorHandler($container->get('logger'), $context);

        $result = GraphQL::executeQuery($container->get(Schema::class), $query, null, $context, $variables)
            ->setErrorsHandler($errorHandler);

        return $this->createResponse(count($result->errors) ? 400 : 200, $result->toArray());
    }
}
