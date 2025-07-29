<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain\Exception;

use Exception;

/**
 * Abstract class for localizable exceptions
 */
abstract class LocalizableException extends Exception
{
    protected string $messageId;
    protected array $messageParams;

    public function __construct(string $messageId, array $messageParams = [])
    {
        $message = $messageId;
        if (count($messageParams)) {
            $message .= sprintf(' (%s)', implode(',', $messageParams));
        }
        parent::__construct($message);
        $this->messageId = $messageId;
        $this->messageParams = $messageParams;
    }

    /**
     * Returns a human readable message identifier
     *
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * Returns parameters which can be interpolated into the localized message
     *
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
