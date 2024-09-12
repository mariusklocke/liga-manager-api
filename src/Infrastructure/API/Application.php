<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\ServiceProvider as ApplicationServiceProvider;
use HexagonalPlayground\Infrastructure\API\Event\RequestEvent;
use HexagonalPlayground\Infrastructure\API\Event\ResponseEvent;
use HexagonalPlayground\Infrastructure\API\ServiceProvider as ApiServiceProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\RouteProvider as GraphQLRouteProvider;
use HexagonalPlayground\Infrastructure\API\GraphQL\ServiceProvider as GraphQLServiceProvider;
use HexagonalPlayground\Infrastructure\API\Health\RouteProvider as HealthRouteProvider;
use HexagonalPlayground\Infrastructure\API\Health\ServiceProvider as HealthServiceProvider;
use HexagonalPlayground\Infrastructure\API\Logos\RouteProvider as LogosRouteProvider;
use HexagonalPlayground\Infrastructure\API\Logos\ServiceProvider as LogosServiceProvider;
use HexagonalPlayground\Infrastructure\API\Metrics\RouteProvider as MetricsRouteProvider;
use HexagonalPlayground\Infrastructure\API\Metrics\ServiceProvider as MetricsServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\RateLimitMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\ServiceProvider as SecurityServiceProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\RouteProvider as WebAuthnRouteProvider;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\ServiceProvider as WebAuthnServiceProvider;
use HexagonalPlayground\Infrastructure\ContainerBuilder;
use HexagonalPlayground\Infrastructure\Email\MailServiceProvider;
use HexagonalPlayground\Infrastructure\Filesystem\ServiceProvider as FilesystemServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\EventServiceProvider;
use HexagonalPlayground\Infrastructure\Persistence\Read\ReadRepositoryProvider;
use Iterator;
use Middlewares\TrailingSlash;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
            new DoctrineServiceProvider(),
            new ReadRepositoryProvider(),
            new SecurityServiceProvider(),
            new MailServiceProvider(),
            new EventServiceProvider(),
            new GraphQLServiceProvider(),
            new WebAuthnServiceProvider(),
            new LogosServiceProvider(),
            new ApiServiceProvider(),
            new FilesystemServiceProvider(),
            new MetricsServiceProvider()
        ];

        $container = ContainerBuilder::build($serviceProviders, self::VERSION);

        parent::__construct($container->get(ResponseFactoryInterface::class), $container);

        foreach ($this->getMiddlewares($container) as $middleware) {
            $this->add($middleware);
        }

        $this->group('/api', function (RouteCollectorProxyInterface $group) {
            $routeProviders = [
                new GraphQLRouteProvider(),
                new WebAuthnRouteProvider(),
                new HealthRouteProvider(),
                new LogosRouteProvider(),
                new MetricsRouteProvider()
            ];

            foreach ($routeProviders as $provider) {
                $provider->register($group);
            }
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new RequestEvent($request));
        $response = parent::handle($request);
        $eventDispatcher->dispatch(new ResponseEvent($request, $response));
        return $response;
    }

    /**
     * Returns the middleware stack
     *
     * The first one added is innermost, last one is the outermost.
     * Request travels from the outside to the inside.
     * Response travels from the inside to the outside.
     *
     * @see https://www.slimframework.com/docs/v4/concepts/middleware.html
     *
     * @param ContainerInterface $container
     * @return Iterator
     */
    private function getMiddlewares(ContainerInterface $container): Iterator
    {
        yield $container->get(ContentLengthMiddleware::class);
        yield $container->get(TrailingSlash::class);
        yield $container->get(AuthenticationMiddleware::class);
        yield $container->get(RateLimitMiddleware::class);
        yield $container->get(MaintenanceModeMiddleware::class);
        yield $container->get(ErrorMiddleware::class);
        yield $container->get(LoggingMiddleware::class);
    }
}
