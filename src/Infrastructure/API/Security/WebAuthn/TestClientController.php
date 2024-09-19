<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Application\TemplateRendererInterface;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestClientController extends BaseController
{
    private TemplateRendererInterface $templateRenderer;

    public function __construct(ResponseFactoryInterface $responseFactory, TemplateRendererInterface $templateRenderer)
    {
        parent::__construct($responseFactory);
        $this->templateRenderer = $templateRenderer;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildHtmlResponse($this->templateRenderer->render('WebAuthnClient.html.php', []));
    }
}
