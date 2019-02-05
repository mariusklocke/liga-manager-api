<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;
use Psr\Http\Message\UriInterface;

class SendPasswordResetMailCommand implements CommandInterface
{
    /** @var string */
    private $email;

    /** @var string */
    private $targetPath;

    /** @var UriInterface */
    private $baseUri;

    /**
     * @param string $email
     * @param string $targetPath
     */
    public function __construct($email, $targetPath)
    {
        TypeAssert::assertString($email, 'email');
        TypeAssert::assertString($targetPath, 'targetPath');

        $this->email      = $email;
        $this->targetPath = $targetPath;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    /**
     * @return UriInterface
     */
    public function getBaseUri(): UriInterface
    {
        return $this->baseUri;
    }

    /**
     * @param UriInterface $baseUri
     * @return SendPasswordResetMailCommand
     */
    public function withBaseUri(UriInterface $baseUri): self
    {
        $clone = clone $this;
        $clone->baseUri = $baseUri;

        return $clone;
    }
}