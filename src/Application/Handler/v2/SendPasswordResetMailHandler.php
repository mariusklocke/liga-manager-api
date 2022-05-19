<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\v2\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\Event\Event;

class SendPasswordResetMailHandler
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
     * @param TokenFactoryInterface     $tokenFactory
     * @param UserRepositoryInterface   $userRepository
     * @param TemplateRendererInterface $templateRenderer
     * @param MailerInterface           $mailer
     */
    public function __construct(TokenFactoryInterface $tokenFactory, UserRepositoryInterface $userRepository, TemplateRendererInterface $templateRenderer, MailerInterface $mailer)
    {
        $this->tokenFactory     = $tokenFactory;
        $this->userRepository   = $userRepository;
        $this->templateRenderer = $templateRenderer;
        $this->mailer           = $mailer;
    }

    /**
     * @param SendPasswordResetMailCommand $command
     * @return array|Event[]
     */
    public function __invoke(SendPasswordResetMailCommand $command): array
    {
        try {
            $user = $this->userRepository->findByEmail($command->getEmail());
        } catch (NotFoundException $e) {
            return []; // Simply do nothing, when user cannot be found to prevent user discovery attacks
        }

        $token = $this->tokenFactory->create($user, new DateTimeImmutable('now + 1 day'));

        $targetUri = $command
            ->getBaseUri()
            ->withPath($command->getTargetPath())
            ->withQuery(http_build_query(['token' => $token->encode()]));

        $message = $this->mailer->createMessage(
            [$user->getEmail() => $user->getFullName()],
            'Reset your password',
            $this->templateRenderer->render('PasswordReset.html.php', [
                'title'      => 'Reset your password',
                'userName'   => $user->getFirstName(),
                'targetLink' => $targetUri->__toString()
            ])
        );

        $this->mailer->send($message);

        return [];
    }
}
