<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;
use Psr\Http\Message\UriInterface;

class SendPasswordResetMailCommand implements CommandInterface
{
    /** @var string */
    private $email;

    /** @var UriInterface */
    private $targetUri;

    /**
     * @param string $email
     * @param UriInterface $baseUri
     * @param string $targetPath
     */
    public function __construct($email, UriInterface $baseUri, $targetPath)
    {
        TypeAssert::assertString($email, 'email');
        TypeAssert::assertString($targetPath, 'targetPath');

        $this->email     = $email;
        $this->targetUri = $baseUri->withPath($targetPath);
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