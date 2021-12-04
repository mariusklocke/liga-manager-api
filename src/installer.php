<?php
declare(strict_types=1);

return new class {

    public function __invoke(): void
    {
        $this->output('Installing PHAR dependencies ...');

        foreach ($this->getTools() as $filename => $source) {
            $target = getenv('APP_HOME') . '/bin/' . $filename;

            if (file_exists($target)) {
                continue;
            }

            $this->installTool($target, $source);
        }

        $this->output('Installing composer dependencies ...');
        $this->runCommand('composer.phar install --prefer-dist --no-dev --optimize-autoloader --no-cache --no-progress');
        $this->output('Installation complete.');
    }

    private function output(string $message): void
    {
        echo $message . PHP_EOL;
    }

    private function getTools(): Iterator
    {
        $composerJsonPath = realpath(getenv('APP_HOME') . '/composer.json');
        $composerConfig = json_decode(file_get_contents($composerJsonPath), true);

        if (isset($composerConfig['extra']['phar-tools'])) {
            yield from $composerConfig['extra']['phar-tools'];
        }
    }

    private function installTool(string $target, string $source): void
    {
        $this->output(sprintf('Installing %s ...', $target));
        $result = stream_copy_to_stream(
            fopen($source, 'r'),
            fopen($target, 'w')
        );
        if (false === $result) {
            throw new RuntimeException(sprintf('Failed to install %s', $target));
        }
        chmod($target, 0755);
    }

    private function runCommand(string $command): void
    {
        system($command, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException("Failed to run command '$command'. Exit-Code: $exitCode");
        }
    }
};
