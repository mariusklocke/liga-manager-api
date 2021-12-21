<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Domain\Util\Assert;

abstract class Filter
{
    public const MODE_INCLUDE = 'include';
    public const MODE_EXCLUDE = 'exclude';

    /** @var string */
    protected $field;

    /** @var string */
    protected $mode;

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    public function validate(array $fieldDefinitions): void
    {
        Assert::true(isset($fieldDefinitions[$this->getField()]), 'Invalid EqualityFilter: Unknown field.');
    }
}
