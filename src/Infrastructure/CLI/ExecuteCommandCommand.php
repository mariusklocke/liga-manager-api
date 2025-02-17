<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\InputParser;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommandCommand extends Command 
{
    protected function configure(): void
    {
        $this->setName('app:command:execute');
        $this->setDescription('Execute any command on the command bus interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandType = $this->selectCommandType();
        $params = $this->collectParams($commandType);
        $command = new $commandType(...$params);

        if ($this->confirm($command)) {
            $this->style->success('Command would have been executed.');
        } else {
            $this->style->note('Aborted: Command not executed.');
        }

        return 0;
    }

    private function selectCommandType(): string
    {
        $commands = $this->container->get(CommandInterface::class);

        return $this->style->choice('Please select a command', $commands);
    }

    private function collectParams(string $commandType): array
    {
        $params = [];

        foreach ($this->parseConstructorArgs($commandType) as $name => $type) {
            $isNullable = false;
            if (strpos($type, '|null') > 0) {
                $type = str_replace('|null', '', $type);
                $isNullable = true;
            }

            $isCollection = false;
            if (strpos($type, '[]') > 0) {
                $type = str_replace('[]', '', $type);
                $isCollection = true;
            }
            
            if ($isCollection) {
                $value = $this->askMultipleValues($name, $type, $isNullable);
            } else {
                $value = $this->askSingleValue($name, $type, $isNullable);
            }
            $params[$name] = $value;
        }

        return $params;
    }

    private function confirm(CommandInterface $command): bool
    {
        $this->style->text(var_export($command, true));

        return $this->style->confirm('Are you sure to want execute this command?', false);
    }

    private function askSingleValue(string $name, string $type, bool $nullable): mixed
    {
        $mode  = $nullable ? 'optional' : 'required';
        $value = $this->style->ask("Enter value for $name ($type,$mode)", "");
        $value = $this->parseParamValue($type, $value);

        if ($value === null && !$nullable) {
            throw new RuntimeException("Value for param $name cannot be null");
        }

        return $value;
    }

    private function askMultipleValues(string $name, string $type, bool $nullable): ?array
    {
        $values = [];

        do {
            $value = $this->style->ask("Enter value for $name ($type)", "");
            $value = $this->parseParamValue($type, $value);
        } while ($value !== null);

        if (count($values) === 0 && !$nullable) {
            throw new RuntimeException("Value for param $name cannot be null");
        }

        return count($values) > 0 ? $values : null;
    }

    private function parseConstructorArgs(string $commandType): array
    {
        $constructor = (new ReflectionClass($commandType))->getConstructor();
        if (null === $constructor) {
            return [];
        }

        $constructorDocBlock = $constructor->getDocComment();
        if (!is_string($constructorDocBlock)) {
            return [];
        }

        $args = [];
        foreach (explode("\n", $constructorDocBlock) as $line) {
            $matches = [];
            if (preg_match('/@param\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $type = $matches[1];
                $name = substr($matches[2], 1); // strip $ character
                $args[$name] = $type;
            }
        }

        return $args;
    }

    private function parseParamValue(string $type, string $value): mixed
    {
        if ($value === '') {
            return null;
        }

        // TODO: DatePeriod, TeamIdPair, MatchAppointment
        switch ($type) {
            case 'string':
                return $value;
            case 'int':
            case 'integer':
                return InputParser::parseInteger($value);
            case 'float':
            case 'float|int':
                return InputParser::parseFloat($value);
            case 'DateTimeImmutable':
                return InputParser::parseDateTime($value);
        }

        throw new RuntimeException("Unsupported parameter type: $type");
    }
}