#!/usr/bin/env php
<?php
declare(strict_types=1);

function confirm(string $question): bool {
    echoLine($question);
    $input = trim(fgets(STDIN));

    return $input === 'y' || $input === 'yes';
}

function echoLine(string $line): void {
    echo $line . PHP_EOL;
}

function installComposerPackages(): void {
    echoLine("Installing composer packages ...");
    $args = join(' ', [
        'install',
        '--optimize-autoloader',
        '--no-cache',
        '--no-progress'
    ]);
    $exitCode = null;
    system("bin/composer.phar $args", $exitCode);
    if ($exitCode !== 0) {
        throw new RuntimeException('Failed to install composer packages');
    }
}

function installPharTools(): void {
    echoLine("Installing PHAR tools ...");
    $composerConfig = json_decode(file_get_contents('composer.json'), true);
    foreach ($composerConfig['extra']['phar-tools'] as $tool) {
        if (file_exists($tool['target']) && verifyChecksum($tool['target'], $tool['sha256'])) {
            echoLine("{$tool['target']} already installed: Skipped.");
            continue;
        }
        echoLine("Installing {$tool['target']} ...");
        $readStream = fopen($tool['source'], 'r');
        $writeStream = fopen($tool['target'], 'w');
        $result = stream_copy_to_stream($readStream, $writeStream);
        if (false === $result) {
            throw new RuntimeException("Failed to install {$tool['target']}");
        }
        chmod($tool['target'], 0755);
        if (!verifyChecksum($tool['target'], $tool['sha256'])) {
            throw new RuntimeException("Failed to verify checksum for {$tool['target']}");
        }
    }
}

function verifyChecksum(string $filePath, string $checksum): bool {
    return hash_file('sha256', $filePath) === $checksum;
}

$home = realpath(join(DIRECTORY_SEPARATOR, [__DIR__ , '..']));
chdir($home);
if (file_exists('vendor') && !confirm('Directory "vendor" already exists. Are you sure you want to continue?')) {
    exit(0);
}
installPharTools();
installComposerPackages();
