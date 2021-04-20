<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listens to kernel exceptions for the api paths and returns an api response appropriately.
 * Also prevents Symfony from redirecting to the login page for unauthorized access exceptions.
 */
class ApiExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!str_contains($request->getUri(), '/api/v1/')) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'status' => 'error',
            'message' => $exception->getMessage(),
            'data' => null,
        ]));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 256],
        ];
    }
}
