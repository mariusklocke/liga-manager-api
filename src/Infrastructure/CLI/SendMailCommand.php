<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Email\MessageBody;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:mail:send');
        $this->setDescription('Send a mail using an arbitrary content');
        $this->addArgument('recipient', InputArgument::REQUIRED, 'Recipients mail address');
        $this->addArgument('subject', InputArgument::REQUIRED, 'Mail subject');
        $this->addArgument('content', InputArgument::REQUIRED, 'Mail content text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var MailerInterface $mailer */
        $mailer = $this->container->get(MailerInterface::class);

        $mailer->send(
            [$input->getArgument('recipient') => ''],
            $input->getArgument('subject'),
            new MessageBody(
                $input->getArgument('subject'),
                $input->getArgument('content')
            )
        );

        $this->getStyledIO($input, $output)->success('Mail has been to ' . $input->getArgument('recipient'));

        return 0;
    }
}
