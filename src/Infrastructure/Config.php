<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class Config
{
    public string $adminEmail;
    public string $adminPassword;
    public string $appLogosPath;
    public string $appLogosPublicPath;
    public string $emailSenderAddress;
    public string $emailSenderName;
    public string $emailUrl;
    public string $jwtSecret;
    public string $logLevel;
    public string $logPath;
    public string $maintenanceMode;
    public string $mysqlDatabase;
    public string $mysqlHost;
    public string $mysqlPassword;
    public string $mysqlUser;
    public string $redisHost;

    public function __construct()
    {
        $this->adminEmail = getenv('ADMIN_EMAIL') ?: '';
        $this->adminPassword = getenv('ADMIN_PASSWORD') ?: '';
        $this->appLogosPath = getenv('APP_LOGOS_PATH') ?: '';
        $this->appLogosPublicPath = getenv('APP_LOGOS_PUBLIC_PATH') ?: '/logos';
        $this->emailSenderAddress = getenv('EMAIL_SENDER_ADDRESS') ?: 'noreply@example.com';
        $this->emailSenderName = getenv('EMAIL_SENDER_NAME') ?: 'No Reply';
        $this->emailUrl = getenv('EMAIL_URL') ?: 'null://localhost';
        $this->jwtSecret = getenv('JWT_SECRET') ?: '';
        $this->logLevel = getenv('LOG_LEVEL') ?: 'debug';
        $this->logPath = getenv('LOG_PATH') ?: 'php://stdout';
        $this->maintenanceMode = getenv('MAINTENANCE_MODE') ?: 'off';
        $this->mysqlDatabase = getenv('MYSQL_DATABASE') ?: '';
        $this->mysqlHost = getenv('MYSQL_HOST') ?: '';
        $this->mysqlPassword = getenv('MYSQL_PASSWORD') ?: '';
        $this->mysqlUser = getenv('MYSQL_USER') ?: '';
        $this->redisHost = getenv('REDIS_HOST') ?: '';
    }
}
