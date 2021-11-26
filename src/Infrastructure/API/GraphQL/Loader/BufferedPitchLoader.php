<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;

class BufferedPitchLoader
{
    /** @var PitchRepository */
    private $pitchRepository;

    /** @var array */
    private $byPitchId = [];

    /**
     * @param PitchRepository $pitchRepository
     */
    public function __construct(PitchRepository $pitchRepository)
    {
        $this->pitchRepository = $pitchRepository;
    }

    public function addPitch(string $pitchId): void
    {
        $this->byPitchId[$pitchId] = null;
    }

    public function getByPitch(string $pitchId): ?array
    {
        $pitchIds = array_keys($this->byPitchId, null, true);

        foreach ($this->pitchRepository->findPitchesById($pitchIds) as $id => $pitch) {
            $this->byPitchId[$id] = $pitch;
        }

        return $this->byPitchId[$pitchId] ?? null;
    }
}
