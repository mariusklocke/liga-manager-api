<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Criteria;

use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;

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

    /**
     * @param Field|null $fieldDefinition
     */
    public function validate(?Field $fieldDefinition): void
    {
        if ($fieldDefinition === null) {
            throw new InvalidInputException(sprintf(
                'Invalid Filter: Field %s is unknown',
                $this->field
            ));
        }
    }
}
