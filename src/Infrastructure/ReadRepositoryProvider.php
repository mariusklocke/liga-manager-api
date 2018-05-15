<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\ReadDbAdapterInterface;
use HexagonalPlayground\Application\Repository\MatchRepository;
use HexagonalPlayground\Application\Repository\PitchRepository;
use HexagonalPlayground\Application\Repository\RankingRepository;
use HexagonalPlayground\Application\Repository\SeasonRepository;
use HexagonalPlayground\Application\Repository\TeamRepository;
use HexagonalPlayground\Application\Repository\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\MysqliReadDbAdapter;
use mysqli;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ReadRepositoryProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container[ReadDbAdapterInterface::class] = function() use ($container) {
            $mysqli = new mysqli(
                getenv('MYSQL_HOST'),
                getenv('MYSQL_USER'),
                getenv('MYSQL_PASSWORD'),
                getenv('MYSQL_DATABASE')
            );
            $mysqli->set_charset('utf8');
            $db = new MysqliReadDbAdapter($mysqli);
            $db->setLogger($container['doctrine.queryLogger']);
            return $db;
        };
        $container[TeamRepository::class] = function() use ($container) {
            return new TeamRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[SeasonRepository::class] = function() use ($container) {
            return new SeasonRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[RankingRepository::class] = function() use ($container) {
            return new RankingRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[PitchRepository::class] = function() use ($container) {
            return new PitchRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[MatchRepository::class] = function() use ($container) {
            return new MatchRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[TournamentRepository::class] = function () use ($container) {
            return new TournamentRepository($container[ReadDbAdapterInterface::class]);
        };
    }
}