<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Email\HtmlMailRenderer;
use HexagonalPlayground\Application\Email\HtmlUtilsTrait;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\Event\Event;

class SendPasswordResetMailHandler
{
    use HtmlUtilsTrait;

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
        $this->userRepository      = $userRepository;
        $this->templateRenderer    = $templateRenderer;
        $this->mailer              = $mailer;
        $this->accessLinkGenerator = $accessLinkGenerator;
    }

    /**
     * @param SendPasswordResetMailCommand $command
     * @return array|Event[]
     */
    public function __invoke(SendPasswordResetMailCommand $command): array
    {
        $renderer = new HtmlMailRenderer();

        try {
            $user = $this->userRepository->findByEmail($command->getEmail());
        } catch (NotFoundException $e) {
            return []; // Simply do nothing, when user cannot be found to prevent user discovery attacks
        }

        $expiresAt  = new DateTimeImmutable('now + 1 day');
        $targetLink = $this->accessLinkGenerator->generateAccessLink($user, $expiresAt, $command->getTargetPath());

        $recipient = [$user->getEmail() => $user->getFullName()];
        $subject   = 'Reset your password';
        $mailData  = [
            'subject' => 'Passwort zurücksetzen',
            'logo' => [
                'src' => 'https://www.wildeligabremen.de/wp-content/uploads/2023/05/cropped-Logo-mit-Schrift_30-Jahre-Kopie_2-e1683381765583.jpg',
                'alt' => 'Wilde Liga Bremen',
            ],
            'content' => [
                'text' => sprintf('Hey %s, nutze den folgenden Link um ein neues Passwort zu vergeben.', $user->getFirstName()),
                'action' => [
                    'href' => $targetLink,
                    'label' => 'Neues Passwort setzen'
                ]
            ],
            'footer' => [
                'hints' => [
                    sprintf('Der Link ist gültig bis: %s', $expiresAt->format('d.m.Y H:i')),
                    'Bitte leite diese E-Mail nicht an eine andere Person weiter.',
                    'Wenn du diese E-Mail wiederholt bekommst ohne sie selbst angefordert zu haben, melde dich bitte beim Admin-Team.'
                ]
            ]
        ];
        $mailBody = $renderer->render($mailData);
        $subject = $mailData['subject'];
        $message = $this->mailer->createMessage($recipient, $subject, $mailBody);

        $this->mailer->send($message);

        return [];
    }
}
