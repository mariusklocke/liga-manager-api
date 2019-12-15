<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Application\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;

class TestClientController
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
     * @return ResponseInterface
     */
    public function show(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write($this->templateRenderer->render('WebAuthnClient.html.php', []));

        return $response;
    }
}