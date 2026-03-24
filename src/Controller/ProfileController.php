<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Profile\ProfileServiceInterface;
use App\Service\Form\FormDataExtractorServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private ProfileServiceInterface $profileService,
        private FormDataExtractorServiceInterface $formDataExtractor
    ) {
    }
    #[Route('/profil', name: 'app_energreen_profil')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/verify-password', name: 'app_profile_verify_password', methods: ['POST'])]
    public function verifyPassword(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Non authentifié']);
        }

        $jsonData = $this->formDataExtractor->extractJsonData($request);
        $password = $jsonData['password'] ?? '';

        if (empty($password)) {
            return $this->json(['success' => false, 'message' => 'Mot de passe requis']);
        }

        $isValid = $this->profileService->verifyPassword($user, $password);

        return $this->json(['success' => $isValid]);
    }

    #[Route('/profil/edit', name: 'app_profile_edit')]
    public function edit(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profil_edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/update', name: 'app_profile_update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérification du token CSRF
        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('update-profile', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Récupération des données du formulaire
        $data = $this->formDataExtractor->extractProfileData($request);

        try {
            $this->profileService->updateProfile($user, $data);
            $this->addFlash('success', 'Profil et logement mis à jour avec succès !');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_profile_edit');
    }

    #[Route('/profil/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('delete-account', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide. Suppression annulée.');
            return $this->redirectToRoute('app_profile_edit');
        }

        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $this->profileService->deleteAccount($user);

        return $this->redirectToRoute('app_home');
    }
}