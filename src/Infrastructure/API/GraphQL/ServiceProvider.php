<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use DI;
use GraphQL\Type\Schema;
use HexagonalPlayground\Application\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Schema::class => DI\create()
                ->constructor([
                    'query' => DI\get(QueryType::class),
                    'mutation' => DI\get(MutationType::class)
                ]),

            __NAMESPACE__ . '\Loader\*Loader' => DI\autowire(),

            Controller::class => DI\autowire()
        ];
    }
}