<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Functional\Framework\HttpClient;
use HexagonalPlayground\Tests\Functional\Framework\RichClient;
use Slim\App;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var RichClient */
    protected $client;

    /** @var App */
    private $app;

    public function setUp()
    {
        $this->app    = Bootstrap::bootstrap();
        $this->client = new RichClient(new HttpClient($this->app));
    }
}
