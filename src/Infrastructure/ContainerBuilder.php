<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class ContainerBuilder
{
    /**
     * @param array|ServiceProviderInterface[] $serviceProviders
     * @param string $version
     * @return ContainerInterface
     */
    public static function build(array $serviceProviders, string $version): ContainerInterface
    {
        $builder = new DI\ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions([
            'app.home' => realpath(join(DIRECTORY_SEPARATOR, [__DIR__ , '..', '..'])),
            'app.version' => $version
        ]);

        foreach ($serviceProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }
}
