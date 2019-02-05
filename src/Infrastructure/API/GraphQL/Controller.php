<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use HexagonalPlayground\Domain\ExceptionInterface;
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

        $result = GraphQL::executeQuery($schema, $query, null, $context, $variables)
            ->setErrorFormatter(function (Error $error) {
                $formatted = FormattedError::createFromException($error, true);
                $previous  = $error->getPrevious();
                if ($previous instanceof ExceptionInterface) {
                    $formatted['message'] = $previous->getMessage();
                    unset($formatted['extensions']);
                }
                return $formatted;
            })
            ->toArray(true);

        return $this->createResponse(200, $result);
    }
}
