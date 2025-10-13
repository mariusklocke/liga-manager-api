<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Insights;

use HexagonalPlayground\Domain\Exception\PermissionException;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\Network\IpAddress;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\ContainerInspector;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use Iterator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends BaseController
{
    private ContainerInterface $container;
    private ContainerInspector $containerInspector;

    public function __construct(ResponseFactoryInterface $responseFactory, ContainerInterface $container)
    {
        parent::__construct($responseFactory);
        $this->container = $container;
        $this->containerInspector = new ContainerInspector();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $this->assertClientIsLocal($request);

        $environment = getenv();
        ksort($environment);

        return $this->buildJsonResponse([
            'app' => [
                'config' => $this->getConfig()->getAll(),
                'container' => $this->containerInspector->inspect($this->container),
                'environment' => $environment,
                'packages' => iterator_to_array($this->getPackages())
            ],
            'php' => [
                'extensions' => get_loaded_extensions(),
                'opcache' => opcache_get_status(),
                'version' => PHP_VERSION
            ]
        ]);
    }

    private function assertClientIsLocal(ServerRequestInterface $request): void
    {
        $clientIp = $request->getServerParams()['REMOTE_ADDR'] ?? '';
        $clientIp !== '' && (new IpAddress($clientIp))->isLocal() || throw new PermissionException('Only available to localhost');
    }

    private function getPackages(): Iterator
    {
        $installedFile = new File($this->container->get('app.home'), 'vendor', 'composer', 'installed.json');
        $installed = json_decode($installedFile->read());

        foreach ($installed->packages as $package) {
            yield $package->name => $package->version;
        }
    }

    private function getConfig(): Config
    {
        return $this->container->get(Config::class);
    }
}
