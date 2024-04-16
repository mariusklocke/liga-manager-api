<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendInviteMailCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\User;

class SendInviteMailHandler implements AuthAwareHandler
{
    private UserRepositoryInterface $userRepository;
    private TemplateRendererInterface $templateRenderer;
    private MailerInterface $mailer;
    private AccessLinkGeneratorInterface $accessLinkGenerator;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param TemplateRendererInterface $templateRenderer
     * @param MailerInterface $mailer
     * @param AccessLinkGeneratorInterface $accessLinkGenerator
     */
    public function __construct(UserRepositoryInterface $userRepository, TemplateRendererInterface $templateRenderer, MailerInterface $mailer, AccessLinkGeneratorInterface $accessLinkGenerator)
    {
        $this->userRepository = $userRepository;
        $this->templateRenderer = $templateRenderer;
        $this->mailer = $mailer;
        $this->accessLinkGenerator = $accessLinkGenerator;
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

        $expiresAt  = new DateTimeImmutable('now + 1 day');
        $targetLink = $this->accessLinkGenerator->generateAccessLink($user, $expiresAt, $command->getTargetPath());

        $recipient = [$user->getEmail() => $user->getFullName()];
        $subject   = 'You have been invited';
        $mailBody  = $this->templateRenderer->render('InviteUser.html.php', [
            'title'      => $subject,
            'userName'   => $user->getFirstName(),
            'targetLink' => $targetLink,
            'validUntil' => $expiresAt
        ]);

        $message = $this->mailer->createMessage($recipient, $subject, $mailBody);

        $this->mailer->send($message);

        return [];
    }
}
