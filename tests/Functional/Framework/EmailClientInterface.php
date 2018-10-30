<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional\Framework;

interface EmailClientInterface
{
    public function getAllEmails(): array;

    public function deleteAllEmails(): void;
}