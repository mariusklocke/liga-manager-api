<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetTestClientAction implements ActionInterface
{
    private TemplateRendererInterface $templateRenderer;
    private ResponseSerializer $responseSerializer;

    /**
     * @param TemplateRendererInterface $templateRenderer
     * @param ResponseSerializer $responseSerializer
     */
    public function __construct(TemplateRendererInterface $templateRenderer, ResponseSerializer $responseSerializer)
    {
        $this->templateRenderer = $templateRenderer;
        $this->responseSerializer = $responseSerializer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responseSerializer->serializeHtml(
            $response,
            $this->templateRenderer->render('WebAuthnClient.html.php', [])
        );
    }
}
