<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Application\TemplateRendererInterface;
use Slim\Http\Response;

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
     * @return Response
     */
    public function show(): Response
    {
        return (new Response())->write($this->templateRenderer->render('WebAuthnClient.html.php', []));
    }
}