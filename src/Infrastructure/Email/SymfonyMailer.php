<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Email\MessageBody;
use HexagonalPlayground\Infrastructure\Config;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SymfonyMailer implements MailerInterface
{
    private Mailer $mailer;
    private Config $config;
    private HtmlMailRenderer $htmlRenderer;
    private TextMailRenderer $textRenderer;

    public function __construct(Mailer $mailer, Config $config, HtmlMailRenderer $htmlRenderer, TextMailRenderer $textRenderer)
    {
        $this->mailer = $mailer;
        $this->config = $config;
        $this->htmlRenderer = $htmlRenderer;
        $this->textRenderer = $textRenderer;
    }

    public function send(array $to, string $subject, MessageBody $body): void
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
        $message->html($this->htmlRenderer->render($body));
        $message->text($this->textRenderer->render($body));

        $this->mailer->send($message);
    }
}
