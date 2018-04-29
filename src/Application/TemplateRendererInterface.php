<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

interface TemplateRendererInterface
{
    public function render(string $template, array $data);
}