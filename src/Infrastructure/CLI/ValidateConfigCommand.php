<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use HexagonalPlayground\Domain\Util\StringUtils;
use HexagonalPlayground\Infrastructure\Config;
use Iterator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ValidateConfigCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setName('app:config:validate');
        $this->setDescription('Validate the config (does not check backing services connection)');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Config $config */
        $config = $this->container->get(Config::class);
        $io = $this->getStyledIO($input, $output);
        $errors = 0;

        foreach ($this->getValidators($config) as $validator) {
            try {
                $validator();
            } catch (Throwable $exception) {
                $io->error($exception->getMessage());
                $errors++;
            }
        }

        if ($errors > 0) {
            $io->error("The config is invalid: $errors errors detected.");
            return 1;
        }
        
        $io->success('The config is valid.');

        $io->note('This command does NOT check that backing services are available. Run "app:health:check" command for that.');

        return 0;
    }

    /**
     * Yields a collection of validator functions
     * 
     * @param Config $config
     * @return Iterator<callable>
     */
    private function getValidators(Config $config): Iterator
    {
        // Optional: admin.email
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('admin.email') || StringUtils::isValidEmailAddress($config->getValue('admin.email')),
                'Property "admin.email" is not a valid email address',
            );
        };

        // Required: app.base.url
        yield function () use ($config): void {
            $this->assertTrue(
                $config->getValue('app.base.url') && StringUtils::isValidUrl($config->getValue('app.base.url')),
                'Property "app.base.url" is not a valid URL',
            );
        };

        // Optional: db.password.file
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('db.password.file') || is_file($config->getValue('db.password.file')),
                'Property "db.password.file" is not a path to a file',
            );
        };

         // Required: db.url
        yield function () use ($config): void {
            $this->assertTrue(
                $config->getValue('db.url') && StringUtils::isValidUrl($config->getValue('db.url')),
                'Property "db.url" is not a valid URL',
            );
        };

        // Optional: email.sender.address
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('email.sender.address') || StringUtils::isValidEmailAddress($config->getValue('email.sender.address')),
                'Property "email.sender.address" is not a valid email address',
            );
        };

        // Optional: email.url
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('email.url') || StringUtils::isValidUrl($config->getValue('email.url')),
                'Property "email.url" is not a valid URL',
            );
        };

        // Required: jwt.secret OR jwt.secret.file
        yield function () use ($config): void {
            $this->assertTrue(
                $config->getValue('jwt.secret') || $config->getValue('jwt.secret.file'),
                'One of the properties "jwt.secret" or "jwt.secret.file" is required',
            );
        };
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('jwt.secret') || hex2bin($config->getValue('jwt.secret')) !== false,
                'Property "jwt.secret" is not a hex encoded string',
            );
        };
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('jwt.secret.file') || is_file($config->getValue('jwt.secret.file')),
                'Property "jwt.secret.file" is not a path to a file',
            );
        };

        // Optional: log.level
        yield function () use ($config): void {
            $logLevels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
            $this->assertTrue(
                !$config->getValue('log.level') || in_array($config->getValue('log.level'), $logLevels),
                'Property "log.level" is not one of: ' . implode(',', $logLevels),
            );
        };

        // Optional: log.path
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('log.path') || is_resource(fopen($config->getValue('log.path'), 'a')),
                'Property "log.path" is not a path to a writable file',
            );
        };

        // Optional: rate.limit
        yield function () use ($config): void {
            $this->assertTrue(
                !$config->getValue('rate.limit') || preg_match('/^(\d+)r\/(\d+)s$/', $config->getValue('rate.limit')),
                'Property "rate.limit" has invalid format: Use something like "500r/60s"',
            );
        };

        // Required: redis.host
        yield function () use ($config): void {
            $this->assertTrue(
                (bool)$config->getValue('redis.host'),
                'Property "redis.host" must not be empty',
            );
        };
    }

    private function assertTrue(bool $value, string $message): void
    {
        if (!$value) {
            throw new Exception($message);
        }
    }
}
