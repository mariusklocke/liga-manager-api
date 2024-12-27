<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

use RuntimeException;

trait HtmlUtilsTrait
{
    /**
     * Extracts the page title from an HTML mail body
     * 
     * @param string $mailBody
     * @return string
     */
    private function extractTitle(string $mailBody): string
    {
        $matches = [];
        preg_match('/<title>(.+)<\/title>/', $mailBody, $matches);

        if (!isset($matches[1])) {
            throw new RuntimeException('Failed to extract title from mail body');
        }

        return html_entity_decode($matches[1]);
    }
}