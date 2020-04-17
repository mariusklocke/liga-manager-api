<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTestMailCommand extends Command
{
    public const NAME = 'app:send-test-mail';

    protected function configure()
    {
        $this->addArgument('recipient', InputArgument::REQUIRED, 'Sends a test email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MailerInterface $mailer */
        $mailer = $this->container->get(MailerInterface::class);

        $message = $mailer->createMessage();
        $message->setSubject('Test');
        $message->setBody('This is just a test', 'text/plain');
        $message->setTo([$input->getArgument('recipient')]);

        $mailer->send($message);

        $this->getStyledIO($input, $output)->success('Test email has been sent successfully');
    }
}
