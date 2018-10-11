<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetPasswordCommand extends Command
{
    /** @var MailerInterface */
    private $mailer;

    /** @var TemplateRenderer */
    private $templateRenderer;

    /**
     * @param MailerInterface  $mailer
     * @param TemplateRenderer $templateRenderer
     */
    public function __construct(MailerInterface $mailer, TemplateRenderer $templateRenderer)
    {
        $this->mailer = $mailer;
        $this->templateRenderer = $templateRenderer;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('app:reset-password')->setDefinition([
            new InputArgument('email', InputArgument::REQUIRED, "Email address uniquely identifying the user")
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $title    = 'Reset your password';
        $userName = 'Nobody';
        $content  = $this->templateRenderer->render('PasswordReset.html.php', [
            'title'      => $title,
            'userName'   => $userName,
            'targetLink' => 'about:blank'
        ]);

        $message = $this->mailer->createMessage();
        $message->setSubject($title);
        $message->setTo([$input->getArgument('email') => $userName]);
        $message->setBody($content, 'text/html');

        $this->mailer->send($message);

        return parent::execute($input, $output);
    }
}