<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:mail:send');
        $this->setDescription('Send a mail with HTML body');
        $this->addArgument('recipient', InputArgument::REQUIRED, 'Recipients mail address');
        $this->addArgument('subject', InputArgument::REQUIRED, 'Mail subject');
        $this->addArgument('html-file', InputArgument::REQUIRED, 'Path to an HTML file containing the message body');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var MailerInterface $mailer */
        $mailer = $this->container->get(MailerInterface::class);

        $message = $mailer->createMessage(
            [$input->getArgument('recipient') => ''],
            $input->getArgument('subject'),
            file_get_contents($input->getArgument('html-file'))
        );

        $mailer->send($message);

        $this->getStyledIO($input, $output)->success('Mail has been to ' . $input->getArgument('recipient'));

        return 0;
    }
}
