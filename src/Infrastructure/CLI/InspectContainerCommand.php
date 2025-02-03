<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InspectContainerCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:container:inspect');
        $this->setDescription('Inspect the dependency injection container');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $definitionSource = (new ReflectionClass($this->container))->getProperty('definitionSource')->getValue($this->container);

        $entries = [];
        foreach ($definitionSource->getDefinitions() as $key => $definition) {
            $entries[$key] = get_class($definition);
        }
        ksort($entries);

        $headers = ['Key', 'Type'];
        $rows = [];
        foreach ($entries as $key => $type) {
            $rows[] = [$key, $type];
        }

        $this->getStyledIO($input, $output)->table($headers, $rows);

        return 0;
    }
}
