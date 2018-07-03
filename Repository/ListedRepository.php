<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;

use NetBull\SecurityBundle\Entity\Listed;
use NetBull\CoreBundle\Paginator\PaginatorInterface;

/**
 * Class ListedRepository
 * @package NetBull\SecurityBundle\Repository
 */
class ListedRepository extends EntityRepository implements PaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationCount(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select($qb->expr()->countDistinct('l'))
            ->from($this->getEntityName(), 'l')
        ;

        if (isset($params['query']) && '' !== $params['query']) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('l.id', ':qE'),
                    $qb->expr()->like('l.fingerprint', ':qL')
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
        $qb->select('l.id')->groupBy('l.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationQuery(array $params = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('partial l.{id,fingerprint,action}')
            ->from($this->getEntityName(), 'l')
        ;

        return $qb;
    }

    /**
     * @param Listed $listed
     */
    public function save(Listed $listed)
    {
        $this->_em->persist($listed);
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
        $qb = $this->createQueryBuilder('l');

        if (!$action) {
            return $qb->getQuery()->getArrayResult();
        }

        $qb
            ->where($qb->expr()->eq('l.action', ':action'))
            ->setParameter('action', $action)
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
