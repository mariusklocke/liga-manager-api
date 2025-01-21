<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MessageBody;

class TextMailRenderer
{
    public function render(MessageBody $data): string
    {
        $lines = [];
        $lines[] = $data->title;
        $lines[] = '';
        $lines[] = $data->content;
        $lines[] = '';
        
        foreach ($data->actions as $label => $link) {
            $lines[] = $label . ': ' . $link;
        }
        $lines[] = '';
        
        foreach ($data->hints as $hint) {
            $lines[] = '* ' . $hint;
        }

        return implode("\r\n", $lines);
    }
}