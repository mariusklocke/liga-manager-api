<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ConnectionFactory
{
    public function __invoke(ContainerInterface $container): Connection
    {
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);
        /** @var Configuration $doctrineConfig */
        $doctrineConfig = $container->get(Configuration::class);
        /** @var Config $config */
        $config = $container->get(Config::class);
        $params = $this->buildParams($config);
        $customTypes = [
            CustomDateTimeType::class => [
                'dbType' => 'CustomDateTime',
                'doctrineType' => CustomDateTimeType::NAME
            ],
        ];
        $attempt = 1;
        $timeout = 60;
        $startedAt = time();

        do {
            try {
                $connection = DriverManager::getConnection($params, $doctrineConfig);
                $platform   = $connection->getDatabasePlatform();
            } catch (Throwable $exception) {
                $connection = null;
                $platform = null;
                $logger->warning($exception->getMessage(), ['host' => $params['host'], 'attempt' => $attempt]);
                sleep(5);
                if (time() - $startedAt < $timeout) {
                    $attempt++;
                } else {
                    throw $exception;
                }
            }
        } while ($connection === null || $platform === null);

        $logger->debug('Connected to database', ['version' => $connection->getServerVersion()]);

        foreach ($customTypes as $className => $definition) {
            if (!Type::hasType($definition['doctrineType'])) {
                Type::addType($definition['doctrineType'], $className);
            }
            $platform->registerDoctrineTypeMapping($definition['dbType'], $definition['doctrineType']);
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

        return $params;
    }
}