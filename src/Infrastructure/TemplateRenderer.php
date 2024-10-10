<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use InvalidArgumentException;

class TemplateRenderer implements TemplateRendererInterface
{
    private FilesystemService $filesystemService;
    private string $templatePath;

    /**
     * @param FilesystemService $filesystemService
     * @param string $appHome
     */
    public function __construct(FilesystemService $filesystemService, string $appHome)
    {
        $this->filesystemService = $filesystemService;
        $this->templatePath = $filesystemService->joinPaths([$appHome, 'templates']);
        if (!$this->filesystemService->isDirectory($this->templatePath)) {
            throw new InvalidArgumentException('Template directory does not exist');
        }
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data): string
    {
        $path = $this->filesystemService->joinPaths([$this->templatePath, $template]);
        extract($data);
        ob_start();
        require $path;
        return ob_get_clean();
    }
}
