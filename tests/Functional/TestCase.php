<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use HexagonalPlayground\Infrastructure\API\Bootstrap;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    /**
     * @return Client
     */
    protected static function getClient() : Client
    {
        if (null === self::$client) {
            $app = Bootstrap::bootstrap();
            $container = $app->getContainer();
            /** @var EntityManagerInterface $em */
            $em = $container->get('doctrine.entityManager');
            $metadata   = $em->getMetadataFactory()->getAllMetadata();
            $schemaTool = new SchemaTool($em);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
            self::$client = new Client($app);
        }

        return self::$client;
    }
}
