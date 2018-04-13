<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

interface MailerInterface
{
    /**
     * @return MessageInterface
     */
    public function createMessage();

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message);
}