<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

class MessageBody
{
    /**
     * @param string $title Text to display as the title
     * @param string $content Text to display as the main content
     * @param array $actions Assotiative array with action label as key and URL as value
     * @param array $hints Array of text items to serve as hints
     */
    public function __construct(
        public string $title,
        public string $content,
        public array $actions = [],
        public array $hints = []
    ) {
        
    }
}