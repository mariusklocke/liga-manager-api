<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use Psr\Http\Message\UriInterface;

class RequestPasswordResetCommand implements CommandInterface
{
    /** @var string */
    private $email;

    /** @var UriInterface */
    private $targetUri;

    /**
     * @param string $email
     * @param UriInterface $targetUri
     */
    public function __construct(string $email, UriInterface $targetUri)
    {
        $this->email     = $email;
        $this->targetUri = $targetUri;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return UriInterface
     */
    public function getTargetUri(): UriInterface
    {
        return $this->targetUri;
    }
}