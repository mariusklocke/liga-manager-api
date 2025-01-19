<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

interface MailerInterface
{
    /**
     * @param array $to
     * @param string $subject
     * @param MessageBody $body
     */
    public function send(array $to, string $subject, MessageBody $body);
}
