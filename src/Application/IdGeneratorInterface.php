<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

interface IdGeneratorInterface
{
    /**
     * Generates a unique identifier
     *
     * @return string
     */
    public function generate() : string;
}