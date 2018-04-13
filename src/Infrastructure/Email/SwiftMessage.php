<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MessageInterface;

class SwiftMessage extends \Swift_Message implements MessageInterface
{

}