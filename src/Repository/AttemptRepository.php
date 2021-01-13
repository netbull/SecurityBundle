<?php

namespace NetBull\SecurityBundle\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use NetBull\CoreBundle\Paginator\PaginatorRepositoryInterface;
use NetBull\SecurityBundle\Entity\Attempt;

/**
 * Class AttemptRepository
 * @package NetBull\SecurityBundle\Repository
 */
class AttemptRepository extends EntityRepository implements PaginatorRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationCount(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select($qb->expr()->countDistinct('a'));

        if (isset($params['query']) && '' !== $params['query']) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.id', ':qE'),
                    $qb->expr()->like('a.fingerprint', ':qL')
                ))
                ->setParameter('qE', $params['query'])
                ->setParameter('qL', '%' . trim($params['query']) . '%');
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationIds(array $params = []): QueryBuilder
    {
        $qb = $this->getPaginationCount($params);
        $qb->resetDQLPart('select');
        $qb->select('a.id')->groupBy('a.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationQuery(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('partial a.{id,fingerprint,createdAt}');

        return $qb;
    }

    /**
     * @param Attempt $attempt
     * @throws ORMException
     */
    public function save(Attempt $attempt)
    {
        $this->_em->persist($attempt);
        try {
            $this->_em->flush();
        } catch (OptimisticLockException $e) {}
    }

    /**
     * @param DateTime $time
     */
    public function removeOldRecords(DateTime $time)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getEntityName(), 'a')
            ->where($qb->expr()->lte('a.createdAt', ':time'))
            ->setParameter('time', $time)
            ->getQuery()->execute();
    }

    /**
     * @param string $fingerprint
     * @param DateTime $time
     * @return int
     */
    public function countAttempts(string $fingerprint, DateTime $time): int
    {
        $qb = $this->getPaginationCount();
        $qb
            ->where($qb->expr()->andX(
                $qb->expr()->eq('a.fingerprint', ':fingerprint'),
                $qb->expr()->gte('a.createdAt', ':time')
            ))
            ->setParameters([
                'fingerprint' => $fingerprint,
                'time' => $time,
            ]);

        try {
            return (int)$qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException | NoResultException $e) {
            return 0;
        }
    }
}
