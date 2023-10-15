<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTestMailCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:send-test-mail');
        $this->addArgument('recipient', InputArgument::REQUIRED, 'Sends a test email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var MailerInterface $mailer */
        $mailer = $this->container->get(MailerInterface::class);

        $message = $mailer->createMessage(
            [$input->getArgument('recipient') => 'Test Recipient'],
            'Test',
            '<h1>This is just a test</h1>'
        );

        $mailer->send($message);

        $this->getStyledIO($input, $output)->success('Test email has been sent successfully');

        return 0;
    }
}
