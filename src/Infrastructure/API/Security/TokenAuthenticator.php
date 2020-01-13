<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\TokenInterface;
use HexagonalPlayground\Domain\User;

class TokenAuthenticator extends Authenticator
{
    /**
     * Authenticates a user using an existing token
     *
     * The token will be considered invalid if the user password has been changed after the token has been issued
     *
     * @param TokenInterface $token
     * @return AuthContext
     * @throws AuthenticationException
     */
    public function authenticate(TokenInterface $token): AuthContext
    {
        try {
            /** @var User $user */
            $user = $this->userRepository->find($token->getUserId());
        } catch (NotFoundException $e) {
            throw $this->createException();
        }

        $now = new DateTimeImmutable();
        if ($token->getExpiresAt() < $now) {
            throw $this->createException('Token has expired');
        }

        if ($user->hasPasswordChangedSince($token->getIssuedAt())) {
            throw $this->createException('Password has changed after token has been issued.');
        }

        if ($user->haveAccessTokensBeenInvalidatedSince($token->getIssuedAt())) {
            throw $this->createException('Token has been invalidated');
        }

        return new AuthContext($user);
    }
}