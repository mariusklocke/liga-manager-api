<?php

namespace HexagonalPlayground\Application\Command;

class DeletePitchCommand
{
    /** @var string */
    private $pitchId;

    public function __construct(string $pitchId)
    {
        $this->pitchId = $pitchId;
    }

    public function getPitchId() : string
    {
        return $this->pitchId;
    }
}
