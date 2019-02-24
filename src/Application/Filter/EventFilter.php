<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Filter;

class EventFilter
{
    /** @var \DateTimeImmutable|null */
    private $startDate;

    /** @var \DateTimeImmutable|null */
    private $endDate;

    /** @var string|null */
    private $type;

    /**
     * @param \DateTimeImmutable|null $startDate
     * @param \DateTimeImmutable|null $endDate
     * @param string|null $type
     */
    public function __construct(?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null, ?string $type = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}