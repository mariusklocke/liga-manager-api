<?php

namespace HexagonalPlayground\Domain;

interface UuidGeneratorInterface
{
    /**
     * Generates a UUID v4
     *
     * @return string
     */
    public function generateUuid() : string;
}