<?php

namespace App\Service\Profile;

use App\Entity\User;

interface ProfileServiceInterface
{
    /**
     * Vérifie si le mot de passe fourni est valide pour l'utilisateur
     * 
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(User $user, string $password): bool;

    /**
     * Met à jour le profil de l'utilisateur
     * 
     * @param User $user
     * @param array<string, mixed> $data
     * @return void
     * @throws \InvalidArgumentException Si les mots de passe ne correspondent pas
     */
    public function updateProfile(User $user, array $data): void;

    /**
     * Supprime le compte utilisateur
     * 
     * @param User $user
     * @return void
     */
    public function deleteAccount(User $user): void;
}
