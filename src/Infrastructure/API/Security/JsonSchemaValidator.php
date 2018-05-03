<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Route;
use stdClass;

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
        $body      = json_decode($request->getBody()->getContents());
        $validator = new Validator($body, $this->getBodySchema($request));
        $errors    = $validator->errors();
        if (!empty($errors)) {
            $details = array_map(function (ValidationError $error) {
                return $error->toArray();
            }, $errors);
            throw new BadRequestException('JSON schema validation failed', $details);
        }
        return $next($request, $response);
    }

    /**
     * @param Request $request
     * @return stdClass
     */
    private function getBodySchema(Request $request): stdClass
    {
        $route   = $this->getRoute($request);
        $swagger = json_decode(file_get_contents($this->swaggerPath));
        $pattern = str_replace('/api', '', $route->getPattern());
        $method  = strtolower($request->getMethod());

        $parameters = $swagger->paths->{$pattern}->{$method}->parameters;
        foreach ($parameters as $parameter) {
            if ($parameter->in === 'body') {
                return $parameter->schema;
            }
        }

        throw new LogicException('Could not find body schema in swagger specification');
    }

    /**
     * @param Request $request
     * @return Route
     */
    private function getRoute(Request $request): Route
    {
        return $request->getAttribute('route');
    }
}