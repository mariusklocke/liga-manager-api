<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Functional\Framework\SlimClient;
use HexagonalPlayground\Tests\Functional\Framework\RichClient;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var RichClient */
    protected $client;

    public function setUp()
    {
        $this->client = new RichClient(new SlimClient(Bootstrap::bootstrap()));
    }
}
