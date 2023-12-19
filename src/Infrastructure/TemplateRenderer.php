<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use InvalidArgumentException;

class TemplateRenderer implements TemplateRendererInterface
{
    private string $templatePath;

    private FilesystemService $filesystemService;

    /**
     * @param string $templatePath
     */
    public function __construct(string $templatePath)
    {
        $this->filesystemService = new FilesystemService();
        if (!$this->filesystemService->isDirectory($templatePath)) {
            throw new InvalidArgumentException('Template directory does not exist');
        }
        $this->templatePath = $templatePath;
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
