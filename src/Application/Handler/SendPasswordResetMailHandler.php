<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\User;
use Psr\Http\Message\UriInterface;

class SendPasswordResetMailHandler
{
    /** @var TokenFactoryInterface */
    private $tokenFactory;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TemplateRendererInterface */
    private $templateRenderer;

    /** @var MailerInterface */
    private $mailer;

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

    public function __invoke(SendPasswordResetMailCommand $command)
    {
        /** @var User $user */
        $user  = $this->userRepository->findByEmail($command->getEmail());
        $token = $this->tokenFactory->create($user, new DateTimeImmutable('now + 1 day'));

        $targetUri = $command
            ->getBaseUri()
            ->withPath($command->getTargetPath())
            ->withQuery(http_build_query(['token' => $token->encode()]));
        $message = $this->mailer->createMessage();
        $message->setTo([$user->getEmail() => $user->getFullName()]);
        $message->setSubject('Reset your password');
        $message->setBody($this->renderMailBody($user, $targetUri), 'text/html');

        $this->mailer->send($message);
    }

    private function renderMailBody(User $user, UriInterface $targetUri): string
    {
        return $this->templateRenderer->render('PasswordReset.html.php', [
            'title'      => 'Reset your password',
            'userName'   => $user->getFirstName(),
            'targetLink' => $targetUri->__toString()
        ]);
    }
}