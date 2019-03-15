<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Schema;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SchemaProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Schema::class] = function () {
            return new Schema([
                'query'    => new QueryType(),
                'mutation' => new MutationType()
            ]);
        };
    }
}