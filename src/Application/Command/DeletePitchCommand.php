<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class DeletePitchCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $pitchId;

    /**
     * @param string $pitchId
     */
    public function __construct(string $pitchId)
    {
        $this->pitchId = $pitchId;
    }

    /**
     * @return string
     */
    public function getPitchId() : string
    {
        return $this->pitchId;
    }
}
