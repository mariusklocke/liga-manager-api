<?php

declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\MCP\Tool;

use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;

class ListTeams implements ToolInterface
{
    public readonly string $name;
    public readonly string $description;
    public readonly array $inputSchema;
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->name = 'list_teams';
        $this->description = 'List all teams';
        $this->inputSchema = [];
        $this->teamRepository = $teamRepository;
    }

    public function call(array $params): array
    {
        return $this->teamRepository->findMany();
    }
}