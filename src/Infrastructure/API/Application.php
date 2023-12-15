<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\ServiceProvider as ApplicationServiceProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\RouteProvider as GraphQLRouteProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLServiceProvider;
use HexagonalPlayground\Infrastructure\API\Health\RouteProvider as HealthRouteProvider;
use HexagonalPlayground\Infrastructure\API\Health\ServiceProvider as HealthServiceProvider;
use HexagonalPlayground\Infrastructure\API\Logos\RouteProvider as LogosRouteProvider;
use HexagonalPlayground\Infrastructure\API\Logos\ServiceProvider as LogosServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\ServiceProvider as SecurityServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\RouteProvider as WebAuthnRouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\ServiceProvider as WebAuthnServiceProvider;
use HexagonalPlayground\Infrastructure\ContainerBuilder;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use Middlewares\TrailingSlash;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

class Application extends App
{
    public const VERSION = 'development';

    public function __construct()
    {
        $serviceProviders = [
            new HealthServiceProvider(),
            new ApplicationServiceProvider(),
            new LoggerProvider(),
            new DoctrineServiceProvider(),
            new ReadRepositoryProvider(),
            new SecurityServiceProvider(),
            new MailServiceProvider(),
            new EventServiceProvider(),
            new GraphQLServiceProvider(),
            new WebAuthnServiceProvider(),
            new LogosServiceProvider()
        ];

        $container = ContainerBuilder::build($serviceProviders, self::VERSION);
        $responseFactory = new Psr17Factory();

        parent::__construct($responseFactory, $container);

        $this->add(new LoggingMiddleware($container));
        $this->add(new TrailingSlash());
        $this->add(new JsonParserMiddleware());
        $this->add(new AuthenticationMiddleware($container));
        $this->add(new MaintenanceModeMiddleware());

        $errorMiddleware = $this->addErrorMiddleware(false, false, false);
        $errorMiddleware->setDefaultErrorHandler(new ErrorHandler(
            $container->get(LoggerInterface::class),
            $responseFactory,
            $container->get(JsonResponseWriter::class)
        ));

        $this->group('/api', function (RouteCollectorProxyInterface $group) {
            $routeProviders = [
                new GraphQLRouteProvider(),
                new WebAuthnRouteProvider(),
                new HealthRouteProvider(),
                new LogosRouteProvider()
            ];

            foreach ($routeProviders as $provider) {
                $provider->register($group);
            }
        });
    }
}
