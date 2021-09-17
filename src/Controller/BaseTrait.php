<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

trait BaseTrait
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ){

        $this->em = $entityManager;
        $this->userRepository = $userRepository;
    }

}