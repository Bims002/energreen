<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Logger les erreurs selon leur type
        if ($exception instanceof NotFoundHttpException) {
            // 404 - Page non trouvée (ne pas logger en erreur critique)
            $this->logger->warning('Page non trouvée', [
                'exception' => $exception,
                'path' => $event->getRequest()->getPathInfo(),
            ]);
        } elseif ($exception instanceof AccessDeniedHttpException) {
            // 403 - Accès refusé
            $this->logger->warning('Accès refusé', [
                'exception' => $exception,
                'path' => $event->getRequest()->getPathInfo(),
                'user' => $event->getRequest()->getUser(),
            ]);
        } elseif ($exception instanceof HttpExceptionInterface) {
            // Autres erreurs HTTP (400, 401, 500, etc.)
            $statusCode = $exception->getStatusCode();
            
            if ($statusCode >= 500) {
                // Erreurs serveur (500, 502, 503, etc.)
                $this->logger->error('Erreur serveur', [
                    'exception' => $exception,
                    'status_code' => $statusCode,
                    'path' => $event->getRequest()->getPathInfo(),
                ]);
            } else {
                // Erreurs client (400, 401, etc.)
                $this->logger->warning('Erreur client HTTP', [
                    'exception' => $exception,
                    'status_code' => $statusCode,
                    'path' => $event->getRequest()->getPathInfo(),
                ]);
            }
        } else {
            // Exceptions non HTTP (erreurs PHP, etc.)
            $this->logger->error('Exception non gérée', [
                'exception' => $exception,
                'path' => $event->getRequest()->getPathInfo(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }
    }
}
