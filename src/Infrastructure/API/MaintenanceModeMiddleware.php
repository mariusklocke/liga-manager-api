<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    private FilesystemService $filesystemService;
    private string $filePath;

    /**
     * @param FilesystemService $filesystemService
     * @param string $filePath
     */
    public function __construct(FilesystemService $filesystemService, string $filePath)
    {
        $this->filesystemService = $filesystemService;
        $this->filePath = $filePath;
    }

    /**
     * @throws MaintenanceModeException if maintenance mode is enabled
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->filesystemService->isFile($this->filePath)) {
            throw new MaintenanceModeException('API unavailable due to maintenance work.');
        }

        return $handler->handle($request);
    }
}
