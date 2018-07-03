<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;

use NetBull\SecurityBundle\Entity\Attempt;
use NetBull\CoreBundle\Paginator\PaginatorInterface;

/**
 * Class AttemptRepository
 * @package NetBull\SecurityBundle\Repository
 */
class AttemptRepository extends EntityRepository implements PaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationCount(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select($qb->expr()->countDistinct('a'))
            ->from($this->getEntityName(), 'a')
        ;

        if (isset($params['query']) && '' !== $params['query']) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.id', ':qE'),
                    $qb->expr()->like('a.fingerprint', ':qL')
                ))
                ->setParameter('qE', $params['query'])
                ->setParameter('qL', '%' . trim($params['query']) . '%')
            ;
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationIds(array $params = [])
    {
        $qb = $this->getPaginationCount($params);
        $qb->resetDQLPart('select');
        $qb->select('a.id')->groupBy('a.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationQuery(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('partial a.{id,fingerprint,createdAt}')
            ->from($this->getEntityName(), 'a')
        ;

        return $qb;
    }

    /**
     * @param Attempt $attempt
     */
    public function save(Attempt $attempt)
    {
        $this->_em->persist($attempt);
        try {
            $this->_em->flush();
        } catch (OptimisticLockException $e) {}
    }

    /**
     * @param \DateTime $time
     */
    public function removeOldRecords(\DateTime $time)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getEntityName(), 'a')
            ->where($qb->expr()->lte('a.createdAt', ':time'))
            ->setParameter('time', $time)
            ->getQuery()->execute()
        ;
    }

    /**
     * @param string $fingerprint
     * @return int
     */
    public function countAttempts(string $fingerprint)
    {
        $qb = $this->getPaginationCount();
        $qb
            ->where($qb->expr()->eq('a.fingerprint', ':fingerprint'))
            ->setParameter('fingerprint', $fingerprint)
        ;

        try {
            return (int)$qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
