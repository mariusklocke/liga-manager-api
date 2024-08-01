<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CreateUserCommand as CreateUserApplicationCommand;
use HexagonalPlayground\Domain\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:user:create');
        $this->setDescription('Create a user');
        $this->addOption('email', null, InputOption::VALUE_REQUIRED);
        $this->addOption('password', null, InputOption::VALUE_REQUIRED);
        $this->addOption('first-name', null, InputOption::VALUE_REQUIRED);
        $this->addOption('last-name', null, InputOption::VALUE_REQUIRED);
        $this->addOption('role', null, InputOption::VALUE_REQUIRED);
        $this->addOption('default', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = new CreateUserApplicationCommand(
            null,
            $this->getEmail($input, $output),
            $this->getPassword($input, $output),
            $this->getFirstName($input, $output),
            $this->getLastName($input, $output),
            $this->getRole($input, $output),
            []
        );

        $this->container->get(CommandBus::class)->execute($command, $this->getAuthContext());

        $output->writeln('User successfully created.');
        $output->writeln('Email: ' . $command->getEmail());
        $output->writeln('Password: ' . $command->getPassword());

        return 0;
    }

    private function getEmail(InputInterface $input, OutputInterface $output): ?string
    {
        if ($input->getOption('default')) {
            return $this->container->get('config.global.adminEmail');
        }

        if ($input->getOption('email')) {
            return $input->getOption('email');
        }

        return $input->isInteractive() ? $this->getStyledIO($input, $output)->ask('Please enter email address') : null;
    }

    private function getPassword(InputInterface $input, OutputInterface $output): ?string
    {
        if ($input->getOption('default')) {
            return $this->container->get('config.global.adminPassword');
        }

        if ($input->getOption('password')) {
            return $input->getOption('password');
        }

        return $input->isInteractive() ? $this->getStyledIO($input, $output)->ask('Please enter password') : null;
    }

    private function getFirstName(InputInterface $input, OutputInterface $output): ?string
    {
        if ($input->getOption('default')) {
            return 'default';
        }

        if ($input->getOption('first-name')) {
            return $input->getOption('first-name');
        }

        return $input->isInteractive() ? $this->getStyledIO($input, $output)->ask('Please enter first name') : null;
    }

    private function getLastName(InputInterface $input, OutputInterface $output): ?string
    {
        if ($input->getOption('default')) {
            return 'default';
        }

        if ($input->getOption('last-name')) {
            return $input->getOption('last-name');
        }

        return $input->isInteractive() ? $this->getStyledIO($input, $output)->ask('Please enter last name') : null;
    }

    private function getRole(InputInterface $input, OutputInterface $output): ?string
    {
        if ($input->getOption('default')) {
            return User::ROLE_ADMIN;
        }

        if ($input->getOption('role')) {
            return $input->getOption('role');
        }

        if ($input->isInteractive()) {
            return $this->getStyledIO($input, $output)->choice('Choose a role', User::getRoles());
        }

        return null;
    }
}
