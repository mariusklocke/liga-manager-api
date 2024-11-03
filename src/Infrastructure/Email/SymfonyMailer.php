<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Infrastructure\Config;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SymfonyMailer implements MailerInterface
{
    private Mailer $mailer;
    private Config $config;

    public function __construct(Mailer $mailer, Config $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function createMessage(array $to, string $subject, string $html): object
    {
        $message = new Email();

        $message->from(new Address(
            $this->config->getValue('email.sender.address', 'noreply@example.com'),
            $this->config->getValue('email.sender.name', 'No Reply')
        ));

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
