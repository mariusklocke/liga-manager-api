<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Filesystem\File;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    private File $file;

    /**
     * @param string $appHome
     */
    public function __construct(string $appHome)
    {
        $this->file = new File($appHome, '.maintenance');
    }

    /**
     * @throws MaintenanceModeException if maintenance mode is enabled
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->file->exists()) {
            throw new MaintenanceModeException('API unavailable due to maintenance work.');
        }

        return $handler->handle($request);
    }
}
