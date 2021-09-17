<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Util\Basket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

trait BaseTrait
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private Basket $basket;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Basket $basket
    ){

        $this->em = $entityManager;
        $this->userRepository = $userRepository;
        $this->basket = $basket;
    }

}