<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return QueryBuilder
     */
    private function commonJoin(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('category')
            ->innerJoin('p.category', 'category');
    }

    /**
     * @param array $searchParameters
     * @param string|int $hydrationMode
     * @return array
     */
    public function search(array $searchParameters, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $products = $this->commonJoin()->where('p.id is not null');

        if ($searchParameters['category']) {
            $products->andWhere('category.id=:categoryId')->setParameter('categoryId', $searchParameters['category']);
        }

        if ($searchParameters['isCategoryActive']) {
            $products->andWhere('category.isActive=:categoryActive')->setParameter('categoryActive', $searchParameters['isCategoryActive']);
        }

        if ($searchParameters['name']) {
            $products->andWhere('p.name like "%' . $searchParameters['name'] . '%"');
        }

        if (is_bool($searchParameters['isActive'])) {
            $products->andWhere('p.isActive=:isActive')->setParameter('isActive', $searchParameters['isActive']);
        }

        $page = (int)$searchParameters['page'];
        $offset = (int)$searchParameters['offset'];

        return $this->paginator($products->getQuery(), $page, $offset, $hydrationMode);
    }


    /**
     * @param array $productIds
     * @param string|int $hydrationMode
     * @return array
     */
    public function fetchProductsByIds(array $productIds, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        return $this->commonJoin()
            ->where('p.id in (' . implode(',', $productIds) . ')')
            ->getQuery()
            ->getResult($hydrationMode);
    }
}
