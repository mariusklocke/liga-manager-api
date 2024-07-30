<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

class Metric
{
    private string $name;
    private string $type;
    private string $help;
    private int $value;

    public function __construct(string $name, string $type, string $help, int $value)
    {
        $this->name = $name;
        $this->type = $type;
        $this->help = $help;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
