<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Connection as ConnectionWrapper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ConnectionFactory
{
    public function __invoke(ContainerInterface $container): Connection
    {
        $params = $this->buildParams($container->get(Config::class));
        $customTypes = [
            CustomDateTimeType::class => [
                'dbType' => 'CustomDateTime',
                'doctrineType' => CustomDateTimeType::NAME
            ],
        ];

        /** @var ConnectionWrapper $connection */
        $connection = DriverManager::getConnection($params, $container->get(Configuration::class));
        $connection->setLogger($container->get(LoggerInterface::class));
        $connection->setEventDispatcher($container->get(EventDispatcherInterface::class));

        foreach ($customTypes as $className => $definition) {
            if (!Type::hasType($definition['doctrineType'])) {
                Type::addType($definition['doctrineType'], $className);
            }
            $connection->getDatabasePlatform()->registerDoctrineTypeMapping($definition['dbType'], $definition['doctrineType']);
        }

        return $connection;
    }

    private function buildParams(Config $config): array
    {
        if ($config->getValue('db.url')) {
            $url = parse_url($config->getValue('db.url'));
            $params = [
                'dbname' => ltrim($url['path'], '/'),
                'user' => $url['user'],
                'host' => $url['host'],
                'driver' => str_replace('-', '_', $url['scheme'])
            ];
            if (isset($url['port'])) {
                $params['port'] = (int)$url['port'];
            }
            if (isset($url['pass'])) {
                $params['password'] = $url['pass'];
            }
            $passwordFile = $config->getValue('db.password.file');
        } else {
            $params = [
                'dbname' => $config->getValue('mysql.database'),
                'user' => $config->getValue('mysql.user'),
                'password' => $config->getValue('mysql.password'),
                'host' => $config->getValue('mysql.host'),
                'driver' => 'pdo_mysql'
            ];
            $passwordFile = $config->getValue('mysql.password.file');
        }
        if ($params['driver'] === 'pdo_mysql') {
            $params['driverOptions'] = [\Pdo\Mysql::ATTR_INIT_COMMAND => "SET NAMES utf8"];
        }
        if ($passwordFile) {
            $params['password'] = (new File($passwordFile))->read();
        }
        $params['wrapperClass'] = ConnectionWrapper::class;

        return $params;
    }
}