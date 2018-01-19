<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

interface Identifier
{
    /**
     * @return string
     */
    public function toString() : string;

    /**
     * @param $string
     * @return Identifier
     */
    public static function fromString($string) : Identifier;
}