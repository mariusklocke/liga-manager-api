<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Psr\Container\ContainerInterface;

class SchemaFactory
{
    /**
     * @param ContainerInterface $container
     * @return Schema
     */
    public function __invoke(ContainerInterface $container): Schema
    {
        $config = new SchemaConfig();

        $config->setQuery($this->createType('query', function () use ($container) {
            return $container->get(QueryTypeAggregator::class)->aggregate();
        }));

        $config->setMutation($this->createType('mutation', function () use ($container) {
            return $container->get(MutationTypeAggregator::class)->aggregate();
        }));

        return new Schema($config);
    }

    /**
     * @param string $name
     * @param callable $fields
     * @return ObjectType
     */
    private function createType(string $name, callable $fields): ObjectType
    {
        return new ObjectType([
            'name' => $name,
            'fields' => $fields
        ]);
    }
}
