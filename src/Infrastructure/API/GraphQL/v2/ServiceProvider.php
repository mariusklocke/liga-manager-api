<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use DI;
use GraphQL\Type\Definition\ObjectType;
use HexagonalPlayground\Application\Command\v2\CommandInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\EventType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\MatchType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\PitchType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\SeasonType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\TeamType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\TournamentType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output\UserType;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use Psr\Container\ContainerInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Schema::class => DI\factory(function (ContainerInterface $container) {
                $queryTypes = [
                    EventType::class,
                    MatchType::class,
                    PitchType::class,
                    SeasonType::class,
                    TeamType::class,
                    TournamentType::class,
                    UserType::class
                ];

                $queries = [];
                foreach ($queryTypes as $queryType) {
                    $queryType = TypeRegistry::get($queryType);
                    /** @var QueryTypeInterface $queryType */
                    $queries = array_merge($queries, $queryType->getQueries());
                }

                $mutationMapper = new MutationMapper(new TypeMapper(), new AuthReader());
                $commands = $container->get(CommandInterface::class);
                $mutations = [];
                foreach ($commands as $command) {
                    $mutations = array_merge($mutations, $mutationMapper->getDefinition($command));
                }

                return new Schema([
                    'query' => new ObjectType([
                        'name' => 'query',
                        'fields' => $queries
                    ]),
                    'mutation' => new ObjectType([
                        'name' => 'mutation',
                        'fields' => $mutations
                    ])
                ]);
            })
        ];
    }
}
