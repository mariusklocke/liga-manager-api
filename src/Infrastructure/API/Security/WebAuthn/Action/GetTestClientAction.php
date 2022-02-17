<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\HtmlResponseWriter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetTestClientAction implements ActionInterface
{
    /** @var TemplateRendererInterface */
    private TemplateRendererInterface $templateRenderer;

    /** @var HtmlResponseWriter */
    private HtmlResponseWriter $responseWriter;

    /**
     * @param TemplateRendererInterface $templateRenderer
     * @param HtmlResponseWriter $responseWriter
     */
    public function __construct(TemplateRendererInterface $templateRenderer, HtmlResponseWriter $responseWriter)
    {
        $this->templateRenderer = $templateRenderer;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responseWriter->write(
            $response,
            $this->templateRenderer->render('WebAuthnClient.html.php', [])
        );
    }
}