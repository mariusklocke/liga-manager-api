<?php

namespace HexagonalDream\Infrastructure\Persistence;

use HexagonalDream\Application\Exception\PersistenceExceptionInterface;

class DoctrineException extends \Exception implements PersistenceExceptionInterface
{

}
