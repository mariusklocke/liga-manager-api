<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

abstract class AbstractRepository
{
    /** @var ReadDbGatewayInterface */
    protected $gateway;

    /** @var Hydrator */
    protected $hydrator;

    public function __construct(ReadDbGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
        $this->hydrator = new Hydrator($this->getFieldDefinitions());
    }

    /**
     * @return array
     */
    abstract protected function getFieldDefinitions(): array;
}
