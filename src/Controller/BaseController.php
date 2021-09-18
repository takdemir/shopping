<?php

namespace App\Controller;

use App\Util\ReplyUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    /**
     * @param string $contentType
     * @param string $value
     * @return bool
     */
    public function checkContentType(string $contentType, string $value = 'application/json'): bool
    {
        return !(false === strpos($contentType, $value));
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