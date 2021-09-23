<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Util\CacheUtil;
use App\Util\ReplyUtils;
use Doctrine\ORM\EntityManagerInterface;

trait BaseTrait
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private CacheUtil $cacheUtil;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository         $userRepository,
        CacheUtil              $cacheUtil
    )
    {
        $this->em = $entityManager;
        $this->userRepository = $userRepository;
        $this->cacheUtil = $cacheUtil;
    }

    /**
     * @param int|null $userId
     * @return array|void
     */
    public function checkUserAuthorisation(int $userId = null)
    {
        if (!$userDetail = $this->getUser()) {
            return ReplyUtils::failure(['message' => 'No user found!']);
        }

        if ($userId && !$this->isGranted('ROLE_ADMIN') && $userId !== $userDetail->getId()) {
            return ReplyUtils::failure(['message' => 'User is not authorised for that process!']);
        }
    }

}