<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use DI;
use GraphQL\Type\Definition\ObjectType;
use HexagonalPlayground\Application\Command\v2\CommandInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\MutationMapper;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use Psr\Container\ContainerInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Schema::class => DI\factory(function (ContainerInterface $container) {
                /** @var QueryTypeInterface[] $queryTypes */
                $queryTypes = [
                    new EventType(),
                    new MatchType(),
                    new PitchType(),
                    new SeasonType(),
                    new TeamType(),
                    new TournamentType(),
                    new UserType()
                ];

                $queries = [];
                foreach ($queryTypes as $queryType) {
                    $queries = array_merge($queries, $queryType->getQueries());
                }

                /** @var MutationMapper $mutationMapper */
                $mutationMapper = $container->get(MutationMapper::class);
                $commands = [];
                $mutations = array_map(function (string $command) use ($mutationMapper) {
                    return $mutationMapper->getDefinition($command);
                }, $commands);

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
