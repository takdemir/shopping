<?php

namespace App\Repository;

use App\Entity\Discount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Discount|null find($id, $lockMode = null, $lockVersion = null)
 * @method Discount|null findOneBy(array $criteria, array $orderBy = null)
 * @method Discount[]    findAll()
 * @method Discount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discount::class);
    }

    /**
     * @return QueryBuilder
     */
    private function commonJoin(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('user')
            ->addSelect('category')
            ->addSelect('product')
            ->leftJoin('p.user', 'user')
            ->leftJoin('p.category', 'category')
            ->leftJoin('p.product', 'product');
    }

    /**
     * @param array $searchParameters
     * @param string|int $hydrationMode
     * @return array
     */
    public function search(array $searchParameters, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $discounts = $this->commonJoin()->where('p.id is not null');

        if ($searchParameters['user']) {
            $discounts->andWhere('user.id=:user')->setParameter('user', $searchParameters['user']);
        }

        if ($searchParameters['category']) {
            $discounts->andWhere('category.id=:category')->setParameter('category', $searchParameters['category']);
        }

        if ($searchParameters['product']) {
            $discounts->andWhere('product.id=:product')->setParameter('product', $searchParameters['product']);
        }

        if ($searchParameters['discountCodeName']) {
            $discounts->andWhere('p.discountCode=:discountCodeName')->setParameter('discountCodeName', $searchParameters['discountCode']);
        }

        if (is_bool($searchParameters['isActive'])) {
            $discounts->andWhere('p.isActive=:isActive')->setParameter('isActive', $searchParameters['isActive']);
        }

        $page = (int)$searchParameters['page'];
        $offset = (int)$searchParameters['offset'];

        return $this->paginator($discounts->getQuery(), $page, $offset, $hydrationMode);
    }

    /**
     * @param string|int $hydrationMode
     * @return array
     */
    public function fetchAvailableDiscounts(string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $now = new \DateTime();
        return $this->commonJoin()
            ->where('p.isActive=:isActive')->setParameter('isActive', true)
            ->andWhere('p.startAt>=:startAt or p.startAt is null')->setParameter('startAt', $now)
            ->andWhere('p.expireAt<=:expireAt or p.expireAt is null')->setParameter('expireAt', $now)
            ->getQuery()
            ->getResult($hydrationMode);
    }
}
