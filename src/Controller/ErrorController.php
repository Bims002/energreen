<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Twig\Environment;

class ErrorController extends AbstractController
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function show(
        Request $request,
        FlattenException $exception,
        DebugLoggerInterface $logger = null
    ): Response {
        return $this->renderErrorPage($exception->getStatusCode());
    }

    public function preview(
        Request $request,
        int $code
    ): Response {
        return $this->renderErrorPage($code);
    }

    private function renderErrorPage(int $statusCode): Response
    {
        // Déterminer quel template utiliser selon le code d'erreur
        $template = match ($statusCode) {
            403 => 'bundles/TwigBundle/Exception/error403.html.twig',
            404 => 'bundles/TwigBundle/Exception/error404.html.twig',
            500 => 'bundles/TwigBundle/Exception/error500.html.twig',
            default => 'bundles/TwigBundle/Exception/error500.html.twig',
        };

        try {
            return new Response(
                $this->twig->render($template, [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? '',
                ]),
                $statusCode
            );
        } catch (\Exception $e) {
            // Si le template n'existe pas, utiliser un template de fallback
            return new Response(
                '<html><body><h1>Erreur ' . $statusCode . '</h1><p>Une erreur est survenue.</p></body></html>',
                $statusCode
            );
        }
    }
}
