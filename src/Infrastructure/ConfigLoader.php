<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class ConfigLoader
{
    public static function load(string $homePath): array
    {
        $jsonPath = $homePath . DIRECTORY_SEPARATOR . 'env.json';
        if (file_exists($jsonPath)) {
            $values = json_decode(file_get_contents($jsonPath), true);
        } else {
            $values = getenv();
        }

        return [
            'config.api.appBaseUrl'           => $values['APP_BASE_URL'] ?? '',
            'config.api.appLogosPath'         => $values['APP_LOGOS_PATH'] ?? '',
            'config.api.appLogosPublicPath'   => $values['APP_LOGOS_PUBLIC_PATH'] ?? '/logos',
            'config.api.jwtSecret'            => $values['JWT_SECRET'] ?? '',
            'config.api.logLevel'             => $values['LOG_LEVEL'] ?? 'debug',
            'config.api.logPath'              => $values['LOG_PATH'] ?? '',
            'config.api.rateLimit'            => $values['RATE_LIMIT'] ?? '',
            'config.email.emailUrl'           => $values['EMAIL_URL'] ?? 'null://localhost',
            'config.email.emailSenderAddress' => $values['EMAIL_SENDER_ADDRESS'] ?? 'noreply@example.com',
            'config.email.emailSenderName'    => $values['EMAIL_SENDER_NAME'] ?? 'No Reply',
            'config.global.adminEmail'        => $values['ADMIN_EMAIL'] ?? '',
            'config.global.adminPassword'     => $values['ADMIN_PASSWORD'] ?? '',
            'config.mysql.hostname'           => $values['MYSQL_HOST'] ?? '',
            'config.mysql.database'           => $values['MYSQL_DATABASE'] ?? '',
            'config.mysql.username'           => $values['MYSQL_USER'] ?? '',
            'config.mysql.password'           => $values['MYSQL_PASSWORD'] ?? '',
            'config.redis.host'               => $values['REDIS_HOST'] ?? ''
        ];
    }
}
