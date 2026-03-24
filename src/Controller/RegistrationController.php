<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\User\UserRegistrationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RegistrationController extends AbstractController
{
    public function __construct(
        private UserRegistrationServiceInterface $userRegistrationService
    ) {
    }
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, RateLimiterFactory $anonymousApiLimiter): Response
    {
        // 1. Créer le limiteur basé sur l'IP de l'utilisateur
        $limiter = $anonymousApiLimiter->create($request->getClientIp());

        // 2. Vérifier si la limite est dépassée
        // consume(1) retire un jeton. Si false, la limite est atteinte.
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
            // Ou renvoyer un flash message personnalisé
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Enregistrement via le service
            $this->userRegistrationService->registerUser($user, $plainPassword);

            // Rediriger vers la page de connexion après inscription
            $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
