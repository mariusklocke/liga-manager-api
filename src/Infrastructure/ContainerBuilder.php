<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Composer\InstalledVersions;
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
        $package = InstalledVersions::getRootPackage();
        $params = [
            'app.home' => realpath($package['install_path']),
            'app.version' => $package['version'] ?? 'dev-latest',
        ];
        $config = new Config([
            'json' => join(DIRECTORY_SEPARATOR, [$params['app.home'], 'env.json'])
        ]);

        $builder = new DI\ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions($params);
        $builder->addDefinitions([
            HealthCheckInterface::class => [],
            Config::class => $config
        ]);

        foreach ($serviceProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }
}
