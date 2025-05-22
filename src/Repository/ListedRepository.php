<?php

namespace NetBull\SecurityBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use NetBull\CoreBundle\Paginator\PaginatorRepositoryInterface;
use NetBull\SecurityBundle\Entity\Listed;

class ListedRepository extends EntityRepository implements PaginatorRepositoryInterface
{
    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationCount(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select($qb->expr()->countDistinct('l'));

        if (!empty($params['query'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('l.id', ':qE'),
                $qb->expr()->like('l.fingerprint', ':qL')
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
        return $qb->select('l.id')->groupBy('l.id');
    }

    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationQuery(array $params = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');
        return $qb->select('partial l.{id,fingerprint,action}');
    }

    /**
     * @param Listed $listed
     */
    public function save(Listed $listed): void
    {
        $this->getEntityManager()->persist($listed);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string|null $action
     * @return array
     */
    public function getAll(?string $action = null): array
    {
        $qb = $this->createQueryBuilder('l');

        if (!$action) {
            return $qb->getQuery()->getArrayResult();
        }

        $qb->where($qb->expr()->eq('l.action', ':action'))
            ->setParameter('action', $action);

        return $qb->getQuery()->getArrayResult();
    }
}
