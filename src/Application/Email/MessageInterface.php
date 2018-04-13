<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Email;

interface MessageInterface
{
    /**
     * @param array $addresses
     */
    public function setTo($addresses);

    /**
     * @param string $subject
     */
    public function setSubject($subject);

    /**
     * @param string $body
     * @param string $contentType
     */
    public function setBody($body, $contentType);
}