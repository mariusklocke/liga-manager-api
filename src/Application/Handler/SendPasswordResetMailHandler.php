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
use HexagonalPlayground\Application\Translator;
use HexagonalPlayground\Domain\Event\Event;

class SendPasswordResetMailHandler
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
        $this->userRepository      = $userRepository;
        $this->mailer              = $mailer;
        $this->accessLinkGenerator = $accessLinkGenerator;
        $this->translator          = $translator;
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
        $locale     = $user->getLocale();

        $recipient = [$user->getEmail() => $user->getFullName()];
        $mailData  = [
            'title' => $this->translator->get($locale, 'mail.resetPassword.title'),
            'content' => [
                'text' => $this->translator->get($locale, 'mail.resetPassword.content.text', [$user->getFirstName()]),
                'action' => [
                    'href' => $targetLink,
                    'label' => $this->translator->get($locale, 'mail.resetPassword.content.action')
                ]
            ],
            'footer' => [
                'hints' => [
                    $this->translator->get($locale, 'mail.resetPassword.hints.validity', [$expiresAt->format('d.m.Y H:i')]),
                    $this->translator->get($locale, 'mail.resetPassword.hints.disclosure'),
                    $this->translator->get($locale, 'mail.resetPassword.hints.flooding')
                ]
            ]
        ];
        $mailBody = $renderer->render($mailData);
        $subject = $mailData['title'];
        $message = $this->mailer->createMessage($recipient, $subject, $mailBody);

        $this->mailer->send($message);

        return [];
    }
}
