<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

class ContainerCommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface  */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Loads a command.
     *
     * @param string $name
     * @return Command
     * @throws Exception
     */
    public function get($name): Command
    {
        $factories = $this->getCommandFactories();
        if (!isset($factories[$name])) {
            throw new Exception(sprintf(
                "Container does not contain a valid command factory for id '%s'",
                $name
            ));
        }
        $command = call_user_func($factories[$name]);
        if (!($command instanceof Command)) {
            throw new Exception(sprintf(
                "Command factory does not return a valid command object for id '%s'",
                $name
            ));
        }
        return $command;
    }

    /**
     * Checks if a command exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name): bool
    {
        $factories = $this->getCommandFactories();
        return isset($factories[$name]);
    }

    /**
     * @return string[] All registered command names
     */
    public function getNames(): array
    {
        return array_keys($this->getCommandFactories());
    }

    /**
     * @return callable[] An array of registered command factories with their name as key
     */
    private function getCommandFactories(): array
    {
        return $this->container->get('cli.command');
    }
}