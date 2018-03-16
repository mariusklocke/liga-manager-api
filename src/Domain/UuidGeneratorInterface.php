<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

interface UuidGeneratorInterface
{
    /**
     * Generates a unique identifier
     *
     * @return string
     */
    public function generate() : string;
}