<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Lodgment; // Ajout nécessaire
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ProfileController extends AbstractController
{
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
    public function verifyPassword(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Non authentifié']);
        }

        $data = json_decode($request->getContent(), true);
        $password = $data['password'] ?? '';

        if (empty($password)) {
            return $this->json(['success' => false, 'message' => 'Mot de passe requis']);
        }

        $isValid = $hasher->isPasswordValid($user, $password);

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
    public function update(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
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

        $user->setEmail($request->request->get('email'));
        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));

        // --- DEBUT DES AJOUTS POUR LODGMENT ---
        $lodgment = $user->getLodgment();

        // Si l'utilisateur n'a pas de logement, on le crée
        if (!$lodgment) {
            $lodgment = new Lodgment();
            $lodgment->setUser($user);
            $em->persist($lodgment);
        }

        $lodgment->setLodgmentType($request->request->get('lodgment_type'));
        $lodgment->setSurface((int) $request->request->get('surface'));
        $lodgment->setOccupant((int) $request->request->get('occupant'));
        // --- FIN DES AJOUTS POUR LODGMENT ---

        // Hashage du mot de passe s'il est modifié
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_profile_edit');
            }

            $hashedPassword = $hasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        $this->addFlash('success', 'Profil et logement mis à jour avec succès !');
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

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }
}