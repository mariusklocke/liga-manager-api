<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

interface MailerInterface
{
    /**
     * @param array $to
     * @param string $subject
     * @param string $html
     * @return object
     */
    public function createMessage(array $to, string $subject, string $html): object;

    /**
     * @param object $message
     */
    public function send(object $message);
}
