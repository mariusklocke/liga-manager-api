<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use Psr\Container\ContainerInterface;

class ContainerBuilder
{
    /**
     * @param ServiceProviderInterface[] $serverProviders
     * @return ContainerInterface
     */
    public static function build(iterable $serverProviders): ContainerInterface
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

        foreach ($serverProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }
}
