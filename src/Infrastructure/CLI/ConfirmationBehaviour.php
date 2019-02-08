<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ConfirmationBehaviour
{
    public function __construct(Command $command)
    {
        $command->addOption('confirm', 'c', InputOption::VALUE_NONE, '');
    }

    public function hasBeenConfirmed(InputInterface $input, OutputInterface $output, string $message): bool
    {
        if ($input->getOption('confirm')) {
            return true;
        }

        return (new QuestionHelper())->ask($input, $output, new ConfirmationQuestion($message, false));
    }
}