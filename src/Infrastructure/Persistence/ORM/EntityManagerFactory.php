<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Infrastructure\Persistence\QueryLogger;
use PDO;
use Psr\Log\LoggerInterface;

class EntityManagerFactory
{
    public function __invoke(LoggerInterface $logger): EntityManagerInterface
    {
        $config = Setup::createConfiguration(false);
        $driver = new SimplifiedXmlDriver([
            Environment::get('APP_HOME') . "/config/doctrine/Infrastructure/API/Security/WebAuthn"
            => "HexagonalPlayground\\Infrastructure\\API\\Security\\WebAuthn",
            Environment::get('APP_HOME') . "/config/doctrine/Domain"
            => "HexagonalPlayground\\Domain",
            Environment::get('APP_HOME') . "/config/doctrine/Domain/Event"
            => "HexagonalPlayground\\Domain\\Event",
            Environment::get('APP_HOME') . "/config/doctrine/Domain/Value"
            => "HexagonalPlayground\\Domain\\Value"
        ]);
        $driver->setGlobalBasename('global');
        $config->setMetadataDriverImpl($driver);
        $config->setSQLLogger(new QueryLogger($logger));
        $config->setDefaultRepositoryClassName(BaseRepository::class);
        $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);
        $pdo = new PDO(
            'mysql:host=' . Environment::get('MYSQL_HOST') . ';dbname=' . Environment::get('MYSQL_DATABASE'),
            Environment::get('MYSQL_USER'),
            Environment::get('MYSQL_PASSWORD'),
            [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
        );
        $connection = DriverManager::getConnection(['pdo' => $pdo], $config);
        if (!Type::hasType(CustomBinaryType::NAME)) {
            Type::addType(CustomBinaryType::NAME, CustomBinaryType::class);
        }
        if (!Type::hasType(CustomDateTimeType::NAME)) {
            Type::addType(CustomDateTimeType::NAME, CustomDateTimeType::class);
        }
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('CustomBinary', CustomBinaryType::NAME);
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('CustomDateTime', CustomDateTimeType::NAME);
        $em = EntityManager::create($connection, $config);
        $em->getEventManager()->addEventListener(
            [Events::postLoad],
            new DoctrineEmbeddableListener($em, $logger)
        );
        return $em;
    }
}