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
            ->addSelect('order')
            ->addSelect('orderItem')
            ->addSelect('product')
            ->leftJoin('p.orderId', 'order')
            ->leftJoin('p.orderItem', 'orderItem')
            ->leftJoin('orderItem.product', 'product');
    }

    /**
     * @param array $searchParameters
     * @param string|int $hydrationMode
     * @return array
     */
    public function search(array $searchParameters, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $orders = $this->commonJoin()->where('p.id is not null');

        if ($searchParameters['discountReason']) {
            $orders->andWhere('p.discountReason=:discountReason')->setParameter('discountReason', $searchParameters['discountReason']);
        }

        $page = (int)$searchParameters['page'];
        $offset = (int)$searchParameters['offset'];

        return $this->paginator($orders->getQuery(), $page, $offset, $hydrationMode);
    }
}
