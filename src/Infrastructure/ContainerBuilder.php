<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
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

        foreach ($serviceProviders as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        $filesystem = new FilesystemService();
        $builder->addDefinitions([
            'app.home' => $filesystem->getRealPath($filesystem->joinPaths([__DIR__ , '..', '..'])),
            'app.version' => $version
        ]);

        return $builder->build();
    }
}
