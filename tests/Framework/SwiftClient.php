<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Application\Email\MessageInterface;
use HexagonalPlayground\Infrastructure\Email\SwiftMailer;
use Swift_Events_SendEvent;

class SwiftClient implements \Swift_Events_SendListener, EmailClientInterface
{
    private $storage = [];

    public function __construct(SwiftMailer $mailer)
    {
        $mailer->registerPlugin($this);
    }

    /**
     * Invoked immediately before the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        // Nothing
    }

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->storage[] = $evt->getMessage();
    }

    /**
     * @return MessageInterface[]
     */
    public function getAllEmails(): array
    {
        return $this->storage;
    }

    public function deleteAllEmails(): void
    {
        $this->storage = [];
    }
}