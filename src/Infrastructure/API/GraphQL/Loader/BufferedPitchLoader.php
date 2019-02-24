<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

class BufferedPitchLoader
{
    /** @var PitchLoader */
    private $pitchLoader;

    /** @var array */
    private $byPitchId = [];

    /**
     * @param PitchLoader $pitchLoader
     */
    public function __construct(PitchLoader $pitchLoader)
    {
        $this->pitchLoader = $pitchLoader;
    }

    public function addPitch(string $pitchId): void
    {
        $this->byPitchId[$pitchId] = null;
    }

    public function getByPitch(string $pitchId): ?array
    {
        $pitchIds = array_keys($this->byPitchId, null, true);
        foreach ($this->pitchLoader->loadPitchesById($pitchIds) as $id => $pitch) {
            $this->byPitchId[$id] = $pitch;
        }

        return $this->byPitchId[$pitchId] ?? null;
    }
}