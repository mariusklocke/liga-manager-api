<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendInviteMailCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\User;

class SendInviteMailHandler implements AuthAwareHandler
{
    /** @var TokenFactoryInterface */
    private TokenFactoryInterface $tokenFactory;

    /** @var UserRepositoryInterface */
    private UserRepositoryInterface $userRepository;

    /** @var TemplateRendererInterface */
    private TemplateRendererInterface $templateRenderer;

    /** @var MailerInterface */
    private MailerInterface $mailer;

    /**
     * @param TokenFactoryInterface $tokenFactory
     * @param UserRepositoryInterface $userRepository
     * @param TemplateRendererInterface $templateRenderer
     * @param MailerInterface $mailer
     */
    public function __construct(TokenFactoryInterface $tokenFactory, UserRepositoryInterface $userRepository, TemplateRendererInterface $templateRenderer, MailerInterface $mailer)
    {
        $this->tokenFactory = $tokenFactory;
        $this->userRepository = $userRepository;
        $this->templateRenderer = $templateRenderer;
        $this->mailer = $mailer;
    }

    /**
     * @param SendInviteMailCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(SendInviteMailCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var User $user */
        $user  = $this->userRepository->find($command->getUserId());
        $token = $this->tokenFactory->create($user, new DateTimeImmutable('now + 1 week'));

        $targetUri = $command->getBaseUri()
            ->withPath($command->getTargetPath())
            ->withQuery(http_build_query(['token' => $token->encode()]));

        $message = $this->mailer->createMessage(
            [$user->getEmail() => $user->getFullName()],
            'You have been invited',
            $this->templateRenderer->render('InviteUser.html.php', [
                'title'      => 'You have been invited',
                'userName'   => $user->getFirstName(),
                'targetLink' => $targetUri->__toString(),
                'validUntil' => $token->getExpiresAt()
            ])
        );

        $this->mailer->send($message);

        return [];
    }
}
