<?php


namespace App\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

abstract class AbstractRepository extends ServiceEntityRepository
{

    /**
     * @param Query $query
     * @param int $page
     * @param int $offset
     * @param string $hydrationMode
     * @return array
     */
    public function paginator(Query $query, int $page, int $offset, string $hydrationMode): array
    {
        $paginator = new Paginator($query);
        $total = count($paginator);
        $pagesCount = ceil($total / $offset);
        $data = $paginator->getQuery()->setFirstResult(($page - 1) * $offset)->setMaxResults($offset)->getResult($hydrationMode);
        return ['data' => $data, 'pagesCount' => $pagesCount, 'totalDataCount' => $total];
    }

}
