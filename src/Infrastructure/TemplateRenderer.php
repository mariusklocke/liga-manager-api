<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\TemplateRendererInterface;
use InvalidArgumentException;

class TemplateRenderer implements TemplateRendererInterface
{
    private $templatePath;

    /**
     * @param string $templatePath
     */
    public function __construct(string $templatePath)
    {
        if (!is_dir($templatePath)) {
            throw new InvalidArgumentException('Template directory does not exist');
        }
        $this->templatePath = $templatePath;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data)
    {
        $path = $this->templatePath . DIRECTORY_SEPARATOR . $template;
        extract($data);
        ob_start();
        require $path;
        return ob_get_clean();
    }
}