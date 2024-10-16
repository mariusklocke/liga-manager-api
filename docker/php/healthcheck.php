#!/usr/bin/env php
<?php declare(strict_types=1);

function http_request(string $address, int $port): int {
    $command = "curl -sS http://$address:$port/api/health";
    system($command, $exitCode);

    return $exitCode;
}

function fcgi_request(string $address, int $port): int {
    $command = join(' ', [
        'REQUEST_METHOD=GET',
        'REQUEST_URI=/api/health',
        'SCRIPT_NAME=index.php',
        'SCRIPT_FILENAME=$APP_HOME/public/index.php',
        "cgi-fcgi -bind -connect $address:$port"
    ]);
    system($command, $exitCode);

    return $exitCode;
}

function write(string $message, int $indentation = 0): void {
    echo str_repeat(" ", $indentation) . $message . PHP_EOL;
}

$protocol = $argv[1] ?? '';
$address  = $argv[2] ?? '';
$port     = (int)($argv[3] ?? '');
$exitCode = 0;

switch ($protocol) {
    case 'fcgi';
        $port = 9000;
        $exitCode = fcgi_request($address, $port);
        write('');
        break;
    case 'http':
        $port = 8080;
        $exitCode = http_request($address, $port);
        write('');
        break;
    default:
        write('Unsupported socket protocol');
        $exitCode = 1;
        break;
}

exit($exitCode === 0 ? 0 : 1);
