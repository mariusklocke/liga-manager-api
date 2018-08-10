<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

class RollbackService
{
    public function rollback(string $importId)
    {
        $importLogs = $this->findImportLogs($importId);
        foreach ($importLogs as $importLog) {
            // Delete imported entities
        }
    }

    /**
     * @return LogRecord[]
     */
    private function findImportLogs(string $importId): array
    {

    }
}