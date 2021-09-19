<?php

namespace App\Repository;

use App\Entity\OrderDiscount;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderDiscount[]    findAll()
 * @method OrderDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderDiscountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderDiscount::class);
    }

    /**
     * @return QueryBuilder
     */
    private function commonJoin(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('orderId')
            ->addSelect('PARTIAL user.{id, name, email}')
            ->innerJoin('p.orderId', 'orderId')
            ->innerJoin('orderId.user', 'user');
    }

    /**
     * @param array $searchParameters
     * @param string|int $hydrationMode
     * @return array
     */
    public function search(array $searchParameters, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $orderDiscounts = $this->commonJoin()->where('p.id is not null');

        if ($searchParameters['user']) {
            $orderDiscounts->andWhere('user.id=:userId')->setParameter('userId', $searchParameters['user']);
        }

        if ($searchParameters['order']) {
            $orderDiscounts->andWhere('orderId.id=:orderId')->setParameter('orderId', $searchParameters['order']);
        }

        if ($searchParameters['discountReason']) {
            $orderDiscounts->andWhere('p.discountReason=:discountReason')->setParameter('discountReason', $searchParameters['discountReason']);
        }

        $page = (int)$searchParameters['page'];
        $offset = (int)$searchParameters['offset'];

        return $this->paginator($orderDiscounts->getQuery(), $page, $offset, $hydrationMode);
    }
}
