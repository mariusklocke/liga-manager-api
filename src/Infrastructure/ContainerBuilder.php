<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

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
        $builder = new \DI\ContainerBuilder();
        $builder->useAutowiring(true);

        foreach ($serviceProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        return $builder->build();
    }
}
