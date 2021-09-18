<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return QueryBuilder
     */
    private function commonJoin(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('PARTIAL user.{id, name, email}')
            ->addSelect('orderItems')
            ->addSelect('product')
            ->innerJoin('p.user', 'user')
            ->innerJoin('p.orderItems', 'orderItems')
            ->innerJoin('orderItems.product', 'product');
    }

    /**
     * @param array $searchParameters
     * @param string|int $hydrationMode
     * @return array
     */
    public function search(array $searchParameters, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $orders = $this->commonJoin()->where('p.id is not null');

        if ($searchParameters['user']) {
            $orders->andWhere('user.id=:user')->setParameter('user', $searchParameters['user']);
        }

        $page = (int)$searchParameters['page'];
        $offset = (int)$searchParameters['offset'];

        return $this->paginator($orders->getQuery(), $page, $offset, $hydrationMode);
    }

}
