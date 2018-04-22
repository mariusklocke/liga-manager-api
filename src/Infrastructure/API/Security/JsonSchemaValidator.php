<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Route;

class JsonSchemaValidator
{
    /** @var string */
    private $swaggerPath;

    /**
     * @param string $swaggerPath
     */
    public function __construct($swaggerPath)
    {
        $this->swaggerPath = $swaggerPath;
    }

    public function __invoke(Request $request, ResponseInterface $response, callable $next)
    {
        $schema = $this->getBodySchema($request);
        if (is_object($schema)) {
            $body      = json_decode($request->getBody()->getContents());
            $validator = new Validator($body, $schema);
            $errors    = $validator->errors();
            if (!empty($errors)) {
                $details = array_map(function (ValidationError $error) {
                    return $error->toArray();
                }, $errors);
                throw new BadRequestException('JSON Schema Validation failed', $details);
            }
        }
        return $next($request, $response);
    }

    /**
     * @param Request $request
     * @return object|null
     */
    private function getBodySchema(Request $request)
    {
        /** @var Route $route */
        $route = $request->getAttribute('route');
        if (null === $route) {
            return null;
        }
        $swagger = json_decode(file_get_contents($this->swaggerPath));
        $pattern = str_replace('/api', '', $route->getPattern());
        $method  = strtolower($request->getMethod());
        $parameters = $swagger->paths->{$pattern}->{$method}->parameters;
        foreach ($parameters as $parameter) {
            if ($parameter->in === 'body') {
                return $parameter->schema;
            }
        }

        return null;
    }
}