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
        $app = [];
        $app['app.home'] = realpath(join(DIRECTORY_SEPARATOR, [__DIR__ , '..', '..']));
        $app['app.version'] = self::getVersion($app['app.home']);
        $config = ConfigLoader::load($app['app.home']);

        $builder = new DI\ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions([
            HealthCheckInterface::class => []
        ]);
        $builder->addDefinitions($app);
        $builder->addDefinitions($config);

        foreach ($serviceProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }

    /**
     * @param string $home Path to app home
     * @return string
     */
    private static function getVersion(string $home): string
    {
        $package = file_get_contents(join(DIRECTORY_SEPARATOR, [$home, 'composer.json']));
        $package = json_decode($package, true);

        return $package['version'] ?? 'development';
    }
}
