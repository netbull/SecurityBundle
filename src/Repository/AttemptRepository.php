<?php

namespace NetBull\SecurityBundle\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use NetBull\CoreBundle\Paginator\PaginatorRepositoryInterface;
use NetBull\SecurityBundle\Entity\Attempt;

class AttemptRepository extends EntityRepository implements PaginatorRepositoryInterface
{
    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationCount(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select($qb->expr()->countDistinct('a'));

        if (!empty($params['query'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.id', ':qE'),
                $qb->expr()->like('a.fingerprint', ':qL')
            ))
            ->setParameters([
                'qE' => $params['query'],
                'qL' => '%'.trim($params['query']).'%'
            ]);
        }

        return $qb;
    }

    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationIds(array $params = []): QueryBuilder
    {
        $qb = $this->getPaginationCount($params);
        return $qb->select('a.id')->groupBy('a.id');
    }

    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationQuery(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        return $qb->select('partial a.{id,fingerprint,createdAt}');
    }

    /**
     * @param Attempt $attempt
     */
    public function save(Attempt $attempt)
    {
        $this->_em->persist($attempt);
        $this->_em->flush();
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
