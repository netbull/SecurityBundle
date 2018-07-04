<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;

use NetBull\SecurityBundle\Entity\Ban;
use NetBull\CoreBundle\Paginator\PaginatorInterface;

/**
 * Class BanRepository
 * @package NetBull\SecurityBundle\Repository
 */
class BanRepository extends EntityRepository implements PaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationCount(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select($qb->expr()->countDistinct('b'))
            ->from($this->getEntityName(), 'b')
        ;

        if (isset($params['query']) && '' !== $params['query']) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('b.id', ':qE'),
                    $qb->expr()->like('b.fingerprint', ':qL')
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
        $qb->select('b.id')->groupBy('b.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationQuery(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('partial b.{id,fingerprint,createdAt,expireAt}')
            ->from($this->getEntityName(), 'b')
        ;

        return $qb;
    }

    /**
     * @param Ban $attempt
     */
    public function save(Ban $attempt)
    {
        $this->_em->persist($attempt);
        try {
            $this->_em->flush();
        } catch (OptimisticLockException $e) {}
    }

    /**
     * Removes all expired records
     */
    public function flush()
    {
        $now = new \DateTime('now');
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getEntityName(), 'b')
            ->where($qb->expr()->lte('b.expireAt', ':time'))
            ->setParameter('time', $now)
            ->getQuery()->execute()
        ;
    }

    /**
     * @param string $fingerprint
     * @return bool
     */
    public function isBanned(string $fingerprint)
    {
        $now = new \DateTime('now');
        $qb = $this->getPaginationCount();
        $qb
            ->where($qb->expr()->andX(
                $qb->expr()->eq('b.fingerprint', ':fingerprint'),
                $qb->expr()->orX(
                    $qb->expr()->gte('b.expireAt', ':time'),
                    $qb->expr()->isNull('b.expireAt')
                )
            ))
            ->setParameters([
                'fingerprint' => $fingerprint,
                'time' => $now,
            ])
        ;

        try {
            return 0 < (int)$qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return false;
        }
    }
}