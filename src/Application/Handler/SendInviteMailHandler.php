<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendInviteMailCommand;
use HexagonalPlayground\Application\Email\HtmlMailRenderer;
use HexagonalPlayground\Application\Email\HtmlUtilsTrait;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\Translator;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\User;

class SendInviteMailHandler implements AuthAwareHandler
{
    use HtmlUtilsTrait;

    private UserRepositoryInterface $userRepository;
    private MailerInterface $mailer;
    private AccessLinkGeneratorInterface $accessLinkGenerator;
    private Translator $translator;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param MailerInterface $mailer
     * @param AccessLinkGeneratorInterface $accessLinkGenerator
     * @param Translator $translator
     */
    public function __construct(UserRepositoryInterface $userRepository, MailerInterface $mailer, AccessLinkGeneratorInterface $accessLinkGenerator, Translator $translator)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->accessLinkGenerator = $accessLinkGenerator;
        $this->translator = $translator;
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

        $renderer   = new HtmlMailRenderer();
        $expiresAt  = new DateTimeImmutable('now + 1 day');
        $targetLink = $this->accessLinkGenerator->generateAccessLink($user, $expiresAt, $command->getTargetPath());

        $recipient = [$user->getEmail() => $user->getFullName()];
        $mailData  = [
            'title' => 'Deine Einladung',
            'content' => [
                'text' => sprintf('Hey %s, du wurdest von %s zum Liga-Manager eingeladen.', $user->getFirstName(), $authContext->getUser()->getFirstName()),
                'action' => [
                    'href' => $targetLink,
                    'label' => 'Registrieren'
                ]
            ],
            'footer' => [
                'hints' => [
                    sprintf('Deine Einladung ist gültig bis: %s', $expiresAt->format('d.m.Y H:i')),
                    'Bitte leite deine Einladung nicht an eine andere Person weiter.'
                ]
            ]
        ];
        $mailBody = $renderer->render($mailData);
        $subject  = $mailData['title'];
        $message  = $this->mailer->createMessage($recipient, $subject, $mailBody);

        $this->mailer->send($message);

        return [];
    }
}
