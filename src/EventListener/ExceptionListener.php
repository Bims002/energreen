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
                'user' => $event->getRequest()->getUser() ? $event->getRequest()->getUser()->getUserIdentifier() : 'anonymous',
            ]);
        } elseif ($exception instanceof AccessDeniedHttpException) {
            // 403 - Accès refusé
            $this->logger->warning('Accès refusé', [
                'exception' => $exception,
                'path' => $event->getRequest()->getPathInfo(),
                'user' => $event->getRequest()->getUser() ? $event->getRequest()->getUser()->getUserIdentifier() : 'anonymous',
            ]);
        } elseif ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() >= 500) {
            // Erreurs serveur (5xx)
            $this->logger->error('Erreur serveur', [
                'exception' => $exception,
                'path' => $event->getRequest()->getPathInfo(),
                'user' => $event->getRequest()->getUser() ? $event->getRequest()->getUser()->getUserIdentifier() : 'anonymous',
            ]);
        } else {
            // Toutes les autres exceptions non gérées spécifiquement
            $this->logger->error('Une exception inattendue est survenue', [
                'exception' => $exception,
                'path' => $event->getRequest()->getPathInfo(),
                'user' => $event->getRequest()->getUser() ? $event->getRequest()->getUser()->getUserIdentifier() : 'anonymous',
            ]);
        }
    }
}
