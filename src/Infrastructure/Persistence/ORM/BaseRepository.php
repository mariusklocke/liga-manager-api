<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityRepository;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Domain\Util\StringUtils;

class BaseRepository extends EntityRepository implements
    MatchRepositoryInterface,
    TeamRepositoryInterface,
    SeasonRepositoryInterface,
    PitchRepositoryInterface,
    TournamentRepositoryInterface,
    MatchDayRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $entity = parent::find($id, $lockMode, $lockVersion);
        if (null === $entity) {
            $type = StringUtils::stripNamespace($this->_class->getName());
            throw new NotFoundException(
                sprintf('Cannot find %s with ID %s', $type, $id)
            );
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity): void
    {
        $this->_em->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity): void
    {
        $this->_em->remove($entity);
    }
}