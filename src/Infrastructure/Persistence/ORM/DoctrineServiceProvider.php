<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\Common\Proxy\AbstractProxyFactory;
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
use HexagonalPlayground\Infrastructure\Environment;
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
            $config = Setup::createConfiguration(false);
            $driver = new SimplifiedXmlDriver([
                Environment::get('APP_HOME') . "/config/doctrine" => "HexagonalPlayground\\Domain"
            ]);
            $driver->setGlobalBasename('global');
            $config->setMetadataDriverImpl($driver);
            $config->setSQLLogger($container['doctrine.queryLogger']);
            $config->setDefaultRepositoryClassName(BaseRepository::class);
            $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);
            $pdo = new PDO(
                'mysql:host=' . Environment::get('MYSQL_HOST') . ';dbname=' . Environment::get('MYSQL_DATABASE'),
                Environment::get('MYSQL_USER'),
                Environment::get('MYSQL_PASSWORD'),
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