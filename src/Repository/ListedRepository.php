<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use NetBull\CoreBundle\Paginator\PaginatorRepositoryInterface;
use NetBull\SecurityBundle\Entity\Listed;

/**
 * Class ListedRepository
 * @package NetBull\SecurityBundle\Repository
 */
class ListedRepository extends EntityRepository implements PaginatorRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationCount(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select($qb->expr()->countDistinct('l'));

        if (isset($params['query']) && '' !== $params['query']) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('l.id', ':qE'),
                    $qb->expr()->like('l.fingerprint', ':qL')
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
        $qb->select('l.id')->groupBy('l.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationQuery(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('partial l.{id,fingerprint,action}');

        return $qb;
    }

    /**
     * @param Listed $listed
     * @throws ORMException
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
    public function getAll(?string $action = null): array
    {
        $qb = $this->createQueryBuilder('l');

        if (!$action) {
            return $qb->getQuery()->getArrayResult();
        }

        $qb
            ->where($qb->expr()->eq('l.action', ':action'))
            ->setParameter('action', $action);

        return $qb->getQuery()->getArrayResult();
    }
}
