<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App as SlimApp;
use Throwable;

class App extends SlimApp
{
    /**
     * {@inheritdoc}
     */
    protected function handleException(Exception $e, ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        return $this->handleThrowable($e, $request, $response);
    }

    /**
     * {@inheritdoc}
     */
    protected function handlePhpError(Throwable $e, ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        return $this->handleThrowable($e, $request, $response);
    }

    /**
     * @param Throwable $e
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function handleThrowable(Throwable $e, ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $handler = 'errorHandler';
        if ($this->getContainer()->has($handler)) {
            $callable = $this->getContainer()->get($handler);
            // Call the registered handler
            return call_user_func_array($callable, [$request, $response, $e]);
        }

        // No handler found, so just throw the exception
        throw $e;
    }
}