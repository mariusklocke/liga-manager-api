<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;
use HexagonalPlayground\Domain\User;
use PDO;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
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
        $container[EntityManager::class] = function() use ($container) {
            $isDevMode = (getenv('APP_ENVIRONMENT') === 'development');
            $config = Setup::createConfiguration($isDevMode);
            $driver = new SimplifiedXmlDriver([getenv('APP_HOME') . "/config/doctrine" => "HexagonalPlayground\\Domain"]);
            $driver->setGlobalBasename('global');
            $config->setMetadataDriverImpl($driver);
            $config->setSQLLogger($container['doctrine.queryLogger']);
            $config->setDefaultRepositoryClassName(BaseRepository::class);
            $pdo = new PDO(
                'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=' . getenv('MYSQL_DATABASE'),
                getenv('MYSQL_USER'),
                getenv('MYSQL_PASSWORD'),
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            $connection = DriverManager::getConnection(['pdo' => $pdo], $config);
            $em = EntityManager::create($connection, $config);
            $em->getEventManager()->addEventListener(
                [Events::postLoad],
                new DoctrineEmbeddableListener($em, $container['logger'])
            );
            return $em;
        };
        $container['doctrine.queryLogger'] = function () use ($container) {
            return new DoctrineQueryLogger($container['logger']);
        };
        $container[OrmTransactionWrapperInterface::class] = function() use ($container) {
            return new DoctrineTransactionWrapper($container[EntityManager::class]);
        };
        $container['orm.repository.user'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(User::class);
        };
        $container['orm.repository.team'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(Team::class);
        };
        $container['orm.repository.match'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(Match::class);
        };
        $container['orm.repository.season'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(Season::class);
        };
        $container['orm.repository.tournament'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(Tournament::class);
        };
        $container['orm.repository.pitch'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(Pitch::class);
        };
        $container['orm.repository.matchDay'] = function () use ($container) {
            return $container[EntityManager::class]->getRepository(MatchDay::class);
        };
    }
}