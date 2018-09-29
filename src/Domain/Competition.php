<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\Collection;

abstract class Competition
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var Collection|MatchDay[] */
    protected $matchDays;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}