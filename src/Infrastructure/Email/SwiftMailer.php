<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Email\MessageInterface;
use Swift_Mailer;

class SwiftMailer implements MailerInterface
{
    /** @var Swift_Mailer */
    private $swift;
    /** @var string */
    private $fromAddress;
    /** @var string */
    private $fromName;

    /**
     * @param Swift_Mailer $swift
     * @param string       $fromAddress
     * @param string       $fromName
     */
    public function __construct(Swift_Mailer $swift, string $fromAddress, string $fromName)
    {
        $this->swift = $swift;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message): void
    {
        if (!($message instanceof SwiftMessage)) {
            throw new \InvalidArgumentException('Cannot handle instance of ' . get_class($message));
        }
        $this->swift->send($message);
    }

    /**
     * @return MessageInterface
     */
    public function createMessage(): MessageInterface
    {
        $message = new SwiftMessage();
        $message->setFrom([$this->fromAddress => $this->fromName]);
        return $message;
    }
}