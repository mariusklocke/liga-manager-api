<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;

class BufferedPitchLoader
{
    /** @var PitchRepository */
    private PitchRepository $pitchRepository;

    /** @var array */
    private array $byPitchId = [];

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

        if (count($pitchIds)) {
            $filter = new EqualityFilter(
                $this->pitchRepository->getField('id'),
                Filter::MODE_INCLUDE,
                $pitchIds
            );

            foreach ($this->pitchRepository->findMany([$filter]) as $pitch) {
                $this->byPitchId[$pitch['id']] = $pitch;
            }
        }

        return $this->byPitchId[$pitchId] ?? null;
    }
}
