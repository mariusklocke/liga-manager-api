<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Tests\Functional\Framework\EmailClientInterface;
use HexagonalPlayground\Tests\Functional\Framework\MaildevClient;

class UserTest extends TestCase
{
    /** @var EmailClientInterface */
    private $emailClient;

    public function setUp()
    {
        parent::setUp();
        $this->emailClient = new MaildevClient(getenv('MAILDEV_URI') ?: 'http://localhost');
    }

    public function testPasswordResetSendsAnEmail()
    {
        $this->emailClient->deleteAllEmails();
        $this->client->sendPasswortResetMail('user3@example.com', '/straight/to/hell');

        $tries = 0;
        do {
            usleep(100000);
            $emails = $this->emailClient->getAllEmails();
            $tries++;
        } while (count($emails) === 0 && $tries < 10);

        self::assertCount(1, $emails);
    }
}