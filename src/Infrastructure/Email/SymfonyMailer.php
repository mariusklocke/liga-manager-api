<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SymfonyMailer implements MailerInterface
{
    /** @var Mailer */
    private $mailer;

    /** @var string */
    private $senderAddress;

    /** @var string */
    private $senderName;

    public function __construct(Mailer $mailer, string $senderAddress, string $senderName)
    {
        $this->mailer = $mailer;
        $this->senderAddress = $senderAddress;
        $this->senderName = $senderName;
    }

    public function createMessage(array $to, string $subject, string $html): object
    {
        $message = new Email();

        $message->from(new Address($this->senderAddress, $this->senderName));

        foreach ($to as $address => $name) {
            $message->to(new Address($address, $name));
        }

        $message->subject($subject);
        $message->html($html);

        return $message;
    }

    public function send(object $message): void
    {
        $this->mailer->send($message);
    }
}
