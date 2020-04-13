<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetTestClientAction implements ActionInterface
{
    /** @var TemplateRendererInterface */
    private $templateRenderer;

    /**
     * @param TemplateRendererInterface $templateRenderer
     */
    public function __construct(TemplateRendererInterface $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()->write($this->templateRenderer->render('WebAuthnClient.html.php', []));

        return $response;
    }
}