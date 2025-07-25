<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Filesystem\File;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    private File $file;
    private LoggerInterface $logger;

    /**
     * @param string $appHome
     * @param LoggerInterface $logger
     */
    public function __construct(string $appHome, LoggerInterface $logger)
    {
        $this->file = new File($appHome, '.maintenance');
        $this->logger = $logger;
    }

    /**
     * @throws MaintenanceModeException if maintenance mode is enabled
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->file->exists()) {
            $this->logger->debug('Maintenance mode is enabled');
            throw new MaintenanceModeException('API unavailable due to maintenance work.');
        }

        return $handler->handle($request);
    }
}
