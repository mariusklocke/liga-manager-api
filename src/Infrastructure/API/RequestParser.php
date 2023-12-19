<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use Psr\Http\Message\RequestInterface;
use Throwable;

class RequestParser
{
    /**
     * @param RequestInterface $request
     * @return mixed
     * @throws InvalidInputException
     */
    public function parseJson(RequestInterface $request): mixed
    {
        if (!in_array('application/json', $request->getHeader('Content-Type'))) {
            throw new InvalidInputException('Missing expected Content-Type header "application/json"');
        }

        try {
            return json_decode((string)$request->getBody(), true, 64, JSON_THROW_ON_ERROR);
        } catch (Throwable $throwable) {
            throw new InvalidInputException('Failed to decode JSON from request body', 0, $throwable);
        }
    }
}
