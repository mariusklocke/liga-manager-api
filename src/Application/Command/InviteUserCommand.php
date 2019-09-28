<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use Psr\Http\Message\UriInterface;

class InviteUserCommand implements CommandInterface
{
    use AuthenticationAware;
    use IdAware;

    /** @var string */
    private $email;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $role;

    /** @var string[] */
    private $teamIds;

    /** @var string */
    private $targetPath;

    /** @var UriInterface */
    private $baseUri;

    /**
     * @param string|null $id
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string[] $teamIds
     * @param string $targetPath
     */
    public function __construct(?string $id, string $email, string $firstName, string $lastName, string $role, array $teamIds, string $targetPath)
    {
        $this->setId($id);
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->teamIds = $teamIds;
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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string[]
     */
    public function getTeamIds(): array
    {
        return $this->teamIds;
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
     * @return InviteUserCommand
     */
    public function withBaseUri(UriInterface $baseUri): self
    {
        $clone = clone $this;
        $clone->baseUri = $baseUri;

        return $clone;
    }
}