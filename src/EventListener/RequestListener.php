<?php

namespace App\EventListener;

use App\Util\ReplyUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        //Check the content type and if it is not application/json then return failure message
        $contentType = $event->getRequest()->headers->get('Content-Type');
        if ($contentType !== 'application/json') {
            $response = new JsonResponse(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            $event->setResponse($response);
        }
    }
}