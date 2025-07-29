<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
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

        $token->getExpiresAt() >= $now || throw $this->createException('tokenHasExpired');
        !$user->hasPasswordChangedSince($token->getIssuedAt()) || throw $this->createException('passwordChangedAfterTokenIssued');
        !$user->haveAccessTokensBeenInvalidatedSince($token->getIssuedAt()) || throw $this->createException('tokenHasBeenInvalidated');

        return new AuthContext($user);
    }
}
