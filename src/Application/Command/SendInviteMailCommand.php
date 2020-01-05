<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use Psr\Http\Message\UriInterface;

class SendInviteMailCommand implements CommandInterface
{
    /** @var string */
    private $userId;

    /** @var string */
    private $targetPath;

    /** @var UriInterface */
    private $baseUri;

    /**
     * @param string $userId
     * @param string $targetPath
     */
    public function __construct(string $userId, string $targetPath)
    {
        $this->userId = $userId;
        $this->targetPath = $targetPath;
    }

    /**a
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
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
     * @return SendInviteMailCommand
     */
    public function withBaseUri(UriInterface $baseUri): self
    {
        $clone = clone $this;
        $clone->baseUri = $baseUri;

        return $clone;
    }
}