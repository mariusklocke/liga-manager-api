<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\Exception\PersistenceExceptionInterface;

class DoctrineException extends \Exception implements PersistenceExceptionInterface
{

}
