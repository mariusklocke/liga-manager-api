<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class PsrClientException extends RuntimeException implements ClientExceptionInterface
{

}