<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

class MutationTypeAggregator
{
    /** @var array|string[] */
    private $commands;

    /** @var MutationMapper */
    private $mapper;

    /**
     * @param string[] $commands
     * @param MutationMapper $mapper
     */
    public function __construct(array $commands, MutationMapper $mapper)
    {
        $this->commands = $commands;
        $this->mapper = $mapper;
    }

    public function aggregate(): array
    {
        $result = [];
        foreach ($this->commands as $command) {
            $result += $this->mapper->getDefinition($command);
        }

        return $result;
    }
}