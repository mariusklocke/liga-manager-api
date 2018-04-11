<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class TemplateRenderer
{
    private $templatePath;

    /**
     * @param $templatePath
     */
    public function __construct($templatePath)
    {
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