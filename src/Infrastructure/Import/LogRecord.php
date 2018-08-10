<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use DateTimeImmutable;

class LogRecord
{
    /** @var string */
    private $importId;

    /** @var string */
    private $importedEntityClass;

    /** @var string */
    private $importedEntityId;

    /** @var DateTimeImmutable */
    private $createdAt;

    public function __construct(string $importId, string $importedEntityClass, string $importedEntityId)
    {
        $this->importId = $importId;
        $this->createdAt = new DateTimeImmutable();
        $this->importedEntityClass = $importedEntityClass;
        $this->importedEntityId = $importedEntityId;
    }

    /**
     * @return string
     */
    public function getImportId(): string
    {
        return $this->importId;
    }

    /**
     * @return string
     */
    public function getImportedEntityClass(): string
    {
        return $this->importedEntityClass;
    }

    /**
     * @return string
     */
    public function getImportedEntityId(): string
    {
        return $this->importedEntityId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
