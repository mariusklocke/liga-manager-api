<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Bus\SingleCommandBus;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;

abstract class CommandController
{
    /** @var SingleCommandBus */
    protected $commandBus;

    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param string $expected
     * @throws BadRequestException
     */
    protected function assertTypeExact(string $name, $value, string $expected): void
    {
        $actual = $this->getType($value);
        if ($actual !== $expected) {
            throw new BadRequestException(sprintf(
                "Invalid parameter type for '%s'. Expected: %s. Got: %s",
                $name,
                $expected,
                $actual
            ));
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param array  $allowed
     * @throws BadRequestException
     */
    protected function assertTypeOneOf(string $name, $value, array $allowed = []): void
    {
        $actual = $this->getType($value);
        if (!in_array($actual, $allowed, true)) {
            throw new BadRequestException(sprintf(
                "Invalid parameter type for '%s'. Expected one of: [%s]. Got: %s",
                $name,
                implode(', ', $allowed),
                $actual
            ));
        }
    }

    /**
     * @param $value
     * @return string
     */
    private function getType($value): string
    {
        $type = gettype($value);
        if ($type === 'double') {
            $type = 'float';
        }
        return $type;
    }

    /**
     * @param $email
     * @throws BadRequestException
     */
    protected function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new BadRequestException('Invalid email syntax');
        }
    }
}
