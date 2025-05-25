<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use DI;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory as GuzzleHttpFactory;
use HexagonalPlayground\Infrastructure\API\Application;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Container extends DI\Container
{
    private static ?Container $instance = null;

    public static function getInstance(): Container
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        if (extension_loaded('xdebug')) {
            $definitions = [
                RequestHandlerInterface::class => DI\get(Application::class),
                ClientInterface::class => DI\get(PsrSlimClient::class),
                ServerRequestFactoryInterface::class => DI\get(Psr17Factory::class),
                UploadedFileFactoryInterface::class => DI\get(Psr17Factory::class),
                StreamFactoryInterface::class => DI\get(Psr17Factory::class),
            ];
        } else {
            $definitions = [
                ClientInterface::class => DI\create(GuzzleClient::class)->constructor(['base_uri' => getenv('APP_BASE_URL')]),
                ServerRequestFactoryInterface::class => DI\get(GuzzleHttpFactory::class),
                UploadedFileFactoryInterface::class => DI\get(GuzzleHttpFactory::class),
                StreamFactoryInterface::class => DI\get(GuzzleHttpFactory::class),
            ];
        }
        
        parent::__construct($definitions);
    }
}
