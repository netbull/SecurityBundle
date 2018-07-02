<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;

use NetBull\SecurityBundle\Entity\BlockedIP;
use NetBull\CoreBundle\Paginator\PaginatorInterface;

/**
 * Class BlockedIPRepository
 * @package NetBull\SecurityBundle\Repository
 */
class BlockedIPRepository extends EntityRepository implements PaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationCount(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($qb->expr()->countDistinct('i'))
            ->from($this->getEntityName(), 'i')
        ;

        if(isset($params['query']) && '' !== $params['query']){
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('i.id', ':qE'),
                    $qb->expr()->like('i.ip', ':qL')
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
        $qb->select('i.id')->groupBy('i.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationQuery(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('partial i.{id,ip,attempts,lastAttemptAt,bannedAt}')
            ->from($this->getEntityName(), 'i')
        ;

        return $qb;
    }

    /**
     * @param BlockedIP $blockedIP
     */
    public function save(BlockedIP $blockedIP)
    {
        $this->_em->persist($blockedIP);
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
        $qb->delete($this->getEntityName(), 'i')
            ->where($qb->expr()->lte('i', ':time'))
            ->setParameter('time', $time)
            ->getQuery()->execute()
        ;
    }
}
