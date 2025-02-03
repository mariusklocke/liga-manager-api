<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowConfigCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setName('app:config:show');
        $this->setDescription('Show config');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Config $config */
        $config = $this->container->get(Config::class);
        $keys = [
            'admin.email',
            'app.base.url',
            'app.logos.path',
            'db.password.file',
            'db.url',
            'email.sender.address',
            'email.sender.name',
            'email.url',
            'jwt.secret',
            'jwt.secret.file',
            'log.level',
            'log.path',
            'rate.limit',
            'redis.host'
        ];
        $headers = ['Key', 'Value'];
        $rows = [];
        foreach ($keys as $key) {
            $value = $config->getValue($key);
            if ($value) {
                $rows[] = [$key, $value];
            }
        }

        $this->getStyledIO($input, $output)->table($headers, $rows);

        return 0;
    }
}
