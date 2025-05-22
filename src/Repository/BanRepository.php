<?php

namespace NetBull\SecurityBundle\Repository;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use NetBull\CoreBundle\Paginator\PaginatorRepositoryInterface;
use NetBull\SecurityBundle\Entity\Ban;

class BanRepository extends EntityRepository implements PaginatorRepositoryInterface
{
    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationCount(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select($qb->expr()->countDistinct('b'));

        if (!empty($params['query'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('b.id', ':qE'),
                $qb->expr()->like('b.fingerprint', ':qL')
            ))
            ->setParameters(new ArrayCollection([
                new Parameter('qE', $params['query']),
                new Parameter('qL', '%'.trim($params['query']).'%'),
            ]));
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
        return $qb->select('b.id')->groupBy('b.id');
    }

    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationQuery(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');
        return $qb->select('partial b.{id,fingerprint,createdAt,expireAt}');
    }

    /**
     * @param Ban $attempt
     * @return void
     */
    public function save(Ban $attempt): void
    {
        $this->getEntityManager()->persist($attempt);
        $this->getEntityManager()->flush();
    }

    /**
     * Removes all expired records
     */
    public function flush(): void
    {
        $now = new DateTime('now');
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getEntityName(), 'b')
            ->where($qb->expr()->lte('b.expireAt', ':time'))
            ->setParameter('time', $now)
            ->getQuery()->execute();
    }

    /**
     * @param string $fingerprint
     * @return bool
     */
    public function isBanned(string $fingerprint): bool
    {
        $now = new DateTime('now');
        $qb = $this->getPaginationCount();
        $qb->where($qb->expr()->andX(
            $qb->expr()->eq('b.fingerprint', ':fingerprint'),
            $qb->expr()->orX(
                $qb->expr()->gte('b.expireAt', ':time'),
                $qb->expr()->isNull('b.expireAt')
            )
        ))
        ->setParameters(new ArrayCollection([
            new Parameter('fingerprint', $fingerprint),
            new Parameter('time', $now),
        ]));

        try {
            return 0 < (int)$qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException | NoResultException) {
            return false;
        }
    }
}
