<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractRepository implements PasswordUpgraderInterface
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(ManagerRegistry $registry, UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct($registry, User::class);
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        try {
            $user->setPassword($newHashedPassword);
            $this->_em->persist($user);
            $this->_em->flush();
        } catch (\Exception $exception) {

        }

    }

    /**
     * @return QueryBuilder
     */
    private function commonJoin(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, email, name, isActive, roles}');
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $hydration
     * @return User|null
     * @throws NonUniqueResultException
     */
    public function findUserByUsernameAndPassword(string $username, string $password, string $hydration = AbstractQuery::HYDRATE_OBJECT): ?User
    {
        $user = $this->commonJoin()
            ->where('p.email=:username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult($hydration);

        if (!$user) {
            return null;
        }

        $isPasswordValid = $this->userPasswordHasher->isPasswordValid($user, $password);

        if (!$isPasswordValid) {
            return null;
        }

        return $user;
    }


    /**
     * @param array $searchParameters
     * @param string|int $hydrationMode
     * @return array
     */
    public function search(array $searchParameters, string $hydrationMode = AbstractQuery::HYDRATE_ARRAY): array
    {
        $users = $this->commonJoin()->where('p.id is not null');

        if ($searchParameters['name']) {
            $users->andWhere('p.name like "%' . $searchParameters['name'] . '%"');
        }

        if ($searchParameters['email']) {
            $users->andWhere('p.email=:email')->setParameter('email', $searchParameters['email']);
        }

        if (is_bool($searchParameters['isActive'])) {
            $users->andWhere('p.isActive=:isActive')->setParameter('isActive', $searchParameters['isActive']);
        }

        $page = (int)$searchParameters['page'];
        $offset = (int)$searchParameters['offset'];

        return $this->paginator($users->getQuery(), $page, $offset, $hydrationMode);
    }
}
