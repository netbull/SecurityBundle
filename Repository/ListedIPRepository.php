<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;

use NetBull\SecurityBundle\Entity\ListedIP;
use NetBull\CoreBundle\Paginator\PaginatorInterface;

/**
 * Class ListedIPRepository
 * @package NetBull\SecurityBundle\Repository
 */
class ListedIPRepository extends EntityRepository implements PaginatorInterface
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
            ->select('partial i.{id,ip,action}')
            ->from($this->getEntityName(), 'i')
        ;

        return $qb;
    }

    /**
     * @param ListedIP $listedIP
     */
    public function save(ListedIP $listedIP)
    {
        $this->_em->persist($listedIP);
        try {
            $this->_em->flush();
        } catch (OptimisticLockException $e) {}
    }

    /**
     * @param null|string $action
     * @return array
     */
    public function getAll(?string $action = null)
    {
        $qb = $this->createQueryBuilder('i');

        if (!$action) {
            return $qb->getQuery()->getArrayResult();
        }

        $qb
            ->where($qb->expr()->eq('i.action', ':action'))
            ->setParameter('action', $action)
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
