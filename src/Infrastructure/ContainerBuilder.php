<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class ContainerBuilder
{
    /**
     * @param array|ServiceProviderInterface[] $serviceProviders
     * @return ContainerInterface
     */
    public static function build(array $serviceProviders): ContainerInterface
    {
        $versionFilePath = join(DIRECTORY_SEPARATOR, [getenv('APP_HOME'), 'VERSION']);

        $builder = new DI\ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions(['version' => @file_get_contents($versionFilePath) ?: 'development']);

        foreach ($serviceProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }
}
