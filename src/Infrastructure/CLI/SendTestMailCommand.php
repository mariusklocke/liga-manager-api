<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTestMailCommand extends Command
{
    /** @var MailerInterface */
    private $mailer;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->setName('app:send-test-mail');
        $this->addArgument('recipient', InputArgument::REQUIRED, 'Sends a test email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->mailer->createMessage();
        $message->setSubject('Test');
        $message->setBody('This is just a test', 'text/plain');
        $message->setTo([$input->getArgument('recipient')]);

        $this->mailer->send($message);

        $this->getStyledIO($input, $output)->success('Test email has been sent successfully');
    }
}
