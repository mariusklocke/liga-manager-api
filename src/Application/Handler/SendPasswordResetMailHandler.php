<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\Event\Event;

class SendPasswordResetMailHandler
{
    private TokenServiceInterface $tokenService;
    private UserRepositoryInterface $userRepository;
    private TemplateRendererInterface $templateRenderer;
    private MailerInterface $mailer;

    /**
     * @param TokenServiceInterface     $tokenService
     * @param UserRepositoryInterface   $userRepository
     * @param TemplateRendererInterface $templateRenderer
     * @param MailerInterface           $mailer
     */
    public function __construct(TokenServiceInterface $tokenService, UserRepositoryInterface $userRepository, TemplateRendererInterface $templateRenderer, MailerInterface $mailer)
    {
        $this->tokenService     = $tokenService;
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

        $token = $this->tokenService->create($user, new DateTimeImmutable('now + 1 day'));

        $targetUri = $command
            ->getBaseUri()
            ->withPath($command->getTargetPath())
            ->withQuery(http_build_query(['token' => $this->tokenService->encode($token)]));

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
