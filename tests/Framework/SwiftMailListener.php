<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Infrastructure\Email\SwiftMailer;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;

class SwiftMailListener implements EmailListenerInterface, Swift_Events_SendListener
{
    /** @var array */
    private $buffer;

    /** @var bool */
    private $enabled;

    public function __construct(SwiftMailer $mailer)
    {
        $mailer->registerPlugin($this);
        $this->enabled = false;
    }

    /**
     * @inheritDoc
     */
    public function listen(callable $callable): array
    {
        $this->buffer = [];
        $this->enabled = true;
        $callable();
        $this->enabled = false;
        return $this->buffer;
    }

    /**
     * @inheritDoc
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        // Nothing
    }

    /**
     * @inheritDoc
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        if (!$this->enabled) {
            return;
        }

        $this->buffer[] = $evt->getMessage();
    }
}