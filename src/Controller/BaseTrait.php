<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Util\CacheUtil;
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
}