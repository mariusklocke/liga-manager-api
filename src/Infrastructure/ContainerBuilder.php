<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use DI;
use Iterator;
use HexagonalPlayground\Application\ServiceProvider as ApplicationServiceProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLServiceProvider;
use HexagonalPlayground\Infrastructure\API\Health\ServiceProvider as HealthServiceProvider;
use HexagonalPlayground\Infrastructure\API\Logos\ServiceProvider as LogosServiceProvider;
use HexagonalPlayground\Infrastructure\API\Metrics\ServiceProvider as MetricsServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\ServiceProvider as SecurityServiceProvider;
use HexagonalPlayground\Infrastructure\Filesystem\ServiceProvider as FilesystemServiceProvider;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class ContainerBuilder
{
    /**
     * @param ServiceProviderInterface $additionalServices
     * @return ContainerInterface
     */
    public static function build(ServiceProviderInterface $additionalServices): ContainerInterface
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

        foreach (self::getServiceProviders() as $provider) {
            $builder->addDefinitions($provider->getDefinitions());
        }

        $builder->addDefinitions($additionalServices->getDefinitions());

        return $builder->build();
    }

    /**
     * Returns an iterator for common service providers
     * 
     * @return ServiceProviderInterface[]
     */
    private static function getServiceProviders(): Iterator
    {
        yield new ApplicationServiceProvider();
        yield new DoctrineServiceProvider();
        yield new EventServiceProvider();
        yield new FilesystemServiceProvider();
        yield new GraphQLServiceProvider();
        yield new HealthServiceProvider();
        yield new LogosServiceProvider();
        yield new MailServiceProvider();
        yield new MetricsServiceProvider();
        yield new ReadRepositoryProvider();
        yield new SecurityServiceProvider();
    }
}
