<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

interface IdGeneratorInterface
{
    /**
     * Generates a unique identifier
     *
     * @return string
     */
    public function generate() : string;
}