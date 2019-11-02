<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendInviteMailCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Email\MessageInterface;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\User;
use Psr\Http\Message\UriInterface;

class SendInviteMailHandler
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
     */
    public function __invoke(SendInviteMailCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());

        $user    = $this->userRepository->findById($command->getUserId());
        $message = $this->buildMessage($user, $command->getBaseUri(), $command->getTargetPath());

        $this->mailer->send($message);
    }

    /**
     * @param User $invitedUser
     * @param UriInterface $baseUri
     * @param string $targetPath
     * @return MessageInterface
     */
    private function buildMessage(User $invitedUser, UriInterface $baseUri, string $targetPath): MessageInterface
    {
        $title = 'You have been invited';
        $token = $this->tokenFactory->create($invitedUser, new DateTimeImmutable('now + 1 week'));

        $targetUri = $baseUri
            ->withPath($targetPath)
            ->withQuery(http_build_query(['token' => $token->encode()]));

        $message = $this->mailer->createMessage();
        $message->setTo([$invitedUser->getEmail() => $invitedUser->getFullName()]);
        $message->setSubject($title);
        $message->setBody($this->templateRenderer->render('InviteUser.html.php', [
            'title'      => $title,
            'userName'   => $invitedUser->getFirstName(),
            'targetLink' => $targetUri->__toString(),
            'validUntil' => $token->getExpiresAt()
        ]), 'text/html');

        return $message;
    }
}