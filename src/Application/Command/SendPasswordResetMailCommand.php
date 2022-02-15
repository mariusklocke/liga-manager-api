<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use Psr\Http\Message\UriInterface;

class SendPasswordResetMailCommand implements CommandInterface
{
    /** @var string */
    private string $email;

    /** @var string */
    private string $targetPath;

    /** @var UriInterface */
    private UriInterface $baseUri;

    /**
     * @param string $email
     * @param string $targetPath
     */
    public function __construct(string $email, string $targetPath)
    {
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
