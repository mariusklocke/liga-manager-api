<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class Config
{
    private static ?Config $instance = null;

    public string $adminEmail;
    public string $adminPassword;
    public string $appHome;
    public string $emailSenderAddress;
    public string $emailSenderName;
    public string $emailUrl;
    public string $jwtSecret;
    public string $logLevel;
    public string $logPath;
    public string $mysqlDatabase;
    public string $mysqlHost;
    public string $mysqlPassword;
    public string $mysqlUser;
    public string $redisHost;

    public function __construct()
    {
        $this->adminEmail = getenv('ADMIN_EMAIL') ?: '';
        $this->adminPassword = getenv('ADMIN_PASSWORD') ?: '';
        $this->appHome = getenv('APP_HOME') ?: '';
        $this->emailSenderAddress = getenv('EMAIL_SENDER_ADDRESS') ?: 'noreply@example.com';
        $this->emailSenderName = getenv('EMAIL_SENDER_NAME') ?: 'No Reply';
        $this->emailUrl = getenv('EMAIL_URL') ?: 'null://localhost';
        $this->jwtSecret = getenv('JWT_SECRET') ?: '';
        $this->logLevel = getenv('LOG_LEVEL') ?: 'debug';
        $this->logPath = getenv('LOG_PATH') ?: 'php://stdout';
        $this->mysqlDatabase = getenv('MYSQL_DATABASE') ?: '';
        $this->mysqlHost = getenv('MYSQL_HOST') ?: '';
        $this->mysqlPassword = getenv('MYSQL_PASSWORD') ?: '';
        $this->mysqlUser = getenv('MYSQL_USER') ?: '';
        $this->redisHost = getenv('REDIS_HOST') ?: '';
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return static::$instance;
    }
}
