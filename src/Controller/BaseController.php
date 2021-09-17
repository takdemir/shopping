<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    public function checkContentType(string $contentType, string $value = 'application/json'): bool
    {
        return !(false === strpos($contentType, $value));
    }

}