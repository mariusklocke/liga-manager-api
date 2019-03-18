<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

class Exception extends \RuntimeException
{
    /** @var array */
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct(print_r($errors, true));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}