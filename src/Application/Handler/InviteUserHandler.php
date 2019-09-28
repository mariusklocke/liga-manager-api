<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use DateTimeImmutable;
use HexagonalPlayground\Application\Command\InviteUserCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Email\MessageInterface;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;
use Psr\Http\Message\UriInterface;

class InviteUserHandler
{
    /** @var TokenFactoryInterface */
    private $tokenFactory;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /** @var TemplateRendererInterface */
    private $templateRenderer;

    /** @var MailerInterface */
    private $mailer;

    /**
     * @param TokenFactoryInterface $tokenFactory
     * @param UserRepositoryInterface $userRepository
     * @param TeamRepositoryInterface $teamRepository
     * @param TemplateRendererInterface $templateRenderer
     * @param MailerInterface $mailer
     */
    public function __construct(TokenFactoryInterface $tokenFactory, UserRepositoryInterface $userRepository, TeamRepositoryInterface $teamRepository, TemplateRendererInterface $templateRenderer, MailerInterface $mailer)
    {
        $this->tokenFactory = $tokenFactory;
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->templateRenderer = $templateRenderer;
        $this->mailer = $mailer;
    }


    public function __invoke(InviteUserCommand $command)
    {
        if ($command->getRole() === User::ROLE_ADMIN) {
            IsAdmin::check($command->getAuthenticatedUser());
        }

        $this->userRepository->assertEmailDoesNotExist($command->getEmail());
        $user = new User(
            $command->getId(),
            $command->getEmail(),
            null,
            $command->getFirstName(),
            $command->getLastName(),
            $command->getRole()
        );

        foreach ($command->getTeamIds() as $teamId) {
            /** @var Team $team */
            $team = $this->teamRepository->find($teamId);
            CanManageTeam::check($team, $command->getAuthenticatedUser());
            $user->addTeam($team);
        }
        $this->userRepository->save($user);

        $this->mailer->send($this->buildMessage($user, $command->getBaseUri(), $command->getTargetPath()));
    }

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
            'targetLink' => $targetUri->__toString()
        ]), 'text/html');

        return $message;
    }
}