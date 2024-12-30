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
        $params = [
            'app.home' => getenv('APP_HOME') ?: realpath(__DIR__ . '/../..'),
            'app.version' => getenv('APP_VERSION') ?: 'latest',
        ];
        $config = Config::load([
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
