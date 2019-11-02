<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class JsonResponseParser
{
    public function parse(ResponseInterface $response)
    {
        $this->assertValidContentType($response);

        return json_decode((string) $response->getBody());
    }

    private function assertValidContentType(ResponseInterface $response): void
    {
        if (!in_array('application/json', $response->getHeader('Content-Type'))) {
            throw new RuntimeException('Missing valid Content-Type header in Response');
        }
    }
}
