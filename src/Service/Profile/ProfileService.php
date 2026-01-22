<?php

namespace App\Service\Profile;

use App\Entity\Lodgment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileService implements ProfileServiceInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function verifyPassword(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function updateProfile(User $user, array $data): void
    {
        // Mise à jour des informations de base
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }

        // Gestion du logement
        $lodgment = $user->getLodgment();
        if (!$lodgment) {
            $lodgment = new Lodgment();
            $lodgment->setUser($user);
            $this->entityManager->persist($lodgment);
        }

        if (isset($data['lodgment_type'])) {
            $lodgment->setLodgmentType($data['lodgment_type']);
        }
        if (isset($data['surface'])) {
            $lodgment->setSurface((int) $data['surface']);
        }
        if (isset($data['occupant'])) {
            $lodgment->setOccupant((int) $data['occupant']);
        }

        // Gestion du mot de passe
        if (!empty($data['new_password'])) {
            $confirmPassword = $data['confirm_password'] ?? '';

            if ($data['new_password'] !== $confirmPassword) {
                throw new \InvalidArgumentException('Les mots de passe ne correspondent pas.');
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['new_password']);
            $user->setPassword($hashedPassword);
        }

        $this->entityManager->flush();
    }

    public function deleteAccount(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
